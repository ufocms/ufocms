<?php

/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

final class db_helper {

    public ?string $prefix;

    protected array $_mysqli = [];

    protected ?string $_query;

    protected ?string $_lastQuery;

    protected array $_queryOptions = [];

    protected array $_join = [];

    protected array $_where = [];

    protected array $_joinAnd = [];

    protected array $_having = [];

    protected array $_orderBy = [];

    protected array $_groupBy = [];

    protected string $_tableLockMethod = "READ";

    protected array $_bindParams = [""];

    public int $count = 0;

    public int $totalCount = 0;

    protected $_stmtError;

    protected $_stmtErrno;

    protected bool $isSubQuery = false;

    protected $_lastInsertId = null;

    protected $_updateColumns = null;

    public string $returnType = "array";

    protected bool $_nestJoin = false;

    private string $_tableName = "";

    protected bool $_forUpdate = false;

    protected bool $_lockInShareMode = false;

    protected ?int $_mapKey = null;

    protected $traceStartQ;
    protected $traceEnabled;
    protected $traceStripPrefix;
    public array $trace = [];

    public int $pageLimit = 20;
    public int $totalPages = 0;

    protected array $connectionsSettings = [];
    public string $defConnectionName = "ufocms";

    public bool $autoReconnect = true;
    protected int $autoReconnectCount = 0;

    protected bool $_transaction_in_progress = false;

    public function __construct ($host = null, $username = null, $password = null, $db = null, $port = null, $prefix = null, $charset = 'utf8', $socket = null) {
        // if params were passed as array
        if (is_array($host))
            foreach ($host as $key => $val)
                $$key = $val;

        $this->addConnection("ufocms", [
            'host'     => $host,
            'username' => $username,
            'password' => $password,
            'db\db'    => $db,
            'port'     => $port,
            'socket'   => $socket,
            'charset'  => $charset
        ]);

        $this->prefix = $prefix;
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function connect ($connectionName = "ufocms") {
        if (!isset($this->connectionsSettings[$connectionName]))
            $this->error("DB Connection profile not set : $connectionName");

        $profile = $this->connectionsSettings[$connectionName];
        $params  = array_values($profile);
        $charset = array_pop($params);

        if ($this->isSubQuery)
            return;

        if (empty($profile["host"]) && empty($profile["socket"]))
            $this->error("MySQL host or socket is not set : " . $profile["db\db"]);

        $mysqlic = new ReflectionClass("mysqli");
        $mysqli  = $mysqlic->newInstanceArgs($params);

        /**
         * Check the database connection
         */
        if ($mysqli->connect_error)
            $this->error("Database(" . $profile["db\db"] . ") connection error " . $mysqli->connect_errno . ": " . $mysqli->connect_error);

        if (!empty($charset))
            $mysqli->set_charset($charset);

        $this->_mysqli[$connectionName] = $mysqli;
    }

    /**
     * @return void
     */
    public function disconnectAll ( ) {
        foreach (array_keys($this->_mysqli) as $k)
            $this->disconnect($k);
    }

    /**
     * @param $name
     * @return $this
     * @throws Exception
     */
    public function connection ($name): db_helper {
        if (!isset($this->connectionsSettings[$name]))
            throw new Exception('Connection ' . $name . ' was not added.');

        $this->defConnectionName = $name;
        return $this;
    }

    /**
     * @param string $connection
     * @return void
     */
    public function disconnect (string $connection = "ufocms") {
        if (!isset($this->_mysqli[$connection]))
            return;

        $this->_mysqli[$connection]->close();
        unset($this->_mysqli[$connection]);
    }

    /**
     * @param $name
     * @param array $params
     * @return $this
     */
    public function addConnection ($name, array $params): db_helper {
        $this->connectionsSettings[$name] = [];
        foreach (['host', 'username', 'password', 'db\db', 'port', 'socket', 'charset'] as $k) {
            $prm = $params[$k] ?? null;

            if ($k == 'host') {
                if (is_object($prm))
                    $this->_mysqli[$name] = $prm;

                if (!is_string($prm))
                    $prm = null;
            }

            $this->connectionsSettings[$name][$k] = $prm;
        }
        return $this;
    }
    
    /**
     * @return mixed
     * @throws ReflectionException
     */
    public function mysqli ( ) {
        if (!isset($this->_mysqli[$this->defConnectionName]))
            $this->connect($this->defConnectionName);
        return $this->_mysqli[$this->defConnectionName];
    }

    /**
     * @return $this
     */
    protected function reset ( ): db_helper {
        if ($this->traceEnabled)
            $this->trace[] = [$this->_lastQuery, (microtime(true) - $this->traceStartQ), $this->_traceGetCaller()];

        $this->_where   = [];
        $this->_having  = [];
        $this->_join    = [];
        $this->_joinAnd = [];
        $this->_orderBy = [];
        $this->_groupBy = [];
        $this->_bindParams = [""]; // Create the empty 0 index
        $this->_query = null;
        $this->_queryOptions = [];
        $this->returnType = "array";
        $this->_nestJoin  = false;
        $this->_forUpdate = false;
        $this->_lockInShareMode = false;
        $this->_tableName = "";
        $this->_lastInsertId  = null;
        $this->_updateColumns = null;
        $this->_mapKey = null;

        if (!$this->_transaction_in_progress)
            $this->defConnectionName = "ufocms";

        $this->autoReconnectCount = 0;

        return $this;
    }

    /**
     * @return $this
     */
    public function jsonBuilder ( ): db_helper {
        $this->returnType = "json";
        return $this;
    }

    /**
     * @return $this
     */
    public function arrayBuilder ( ): db_helper {
        $this->returnType = "array";
        return $this;
    }

    /**
     * @return $this
     */
    public function objectBuilder ( ): db_helper {
        $this->returnType = "object";
        return $this;
    }

    /**
     * @param $query
     * @return mixed
     * @throws ReflectionException
     * @throws Exception
     */
    private function queryUnprepared ($query) {
        // Execute query
        $stmt = $this->mysqli()->query($query);

        // Failed?
        if ($stmt !== false)
            return $stmt;

        if ($this->mysqli()->errno === 2006 && $this->autoReconnect === true && $this->autoReconnectCount === 0) {
            $this->connect($this->defConnectionName);
            $this->autoReconnectCount++;
            return $this->queryUnprepared($query);
        }

        throw new Exception(sprintf('Unprepared Query Failed, ERRNO: %u (%s)', $this->mysqli()->errno, $this->mysqli()->error), $this->mysqli()->errno);
    }

    /**
     * @param $query
     * @return array|string|string[]
     */
    public function rawAddPrefix ($query ) {
        $query = str_replace(PHP_EOL, "", $query);
        $query = preg_replace('/\s+/', ' ', $query);

        preg_match_all("/(from|into|update|join) [\\'\\´]?([a-zA-Z0-9_-]+)[\\'\\´]?/i", $query, $matches);
        list($from_table, $from, $table) = $matches;

        return str_replace($table[0], $this->prefix . $table[0], $query);
    }

    /**
     * @param $query
     * @param $bindParams
     * @return array|false|string
     * @throws Exception
     */
    public function rawQuery ($query, $bindParams = null) {
        $query = $this->rawAddPrefix($query);
        $params = [""]; // Create the empty 0 index
        $this->_query = $query;
        $stmt = $this->_prepareQuery();

        if (is_array($bindParams) === true) {
            foreach ($bindParams as $prop => $val) {
                $params[0].= $this->_determineType($val);
                $params[]  = $bindParams[$prop];
            }

            call_user_func_array([$stmt, 'bind_param'], $this->refValues($params));
        }

        $stmt->execute();
        $this->count = $stmt->affected_rows;
        $this->_stmtError = $stmt->error;
        $this->_stmtErrno = $stmt->errno;
        $this->_lastQuery = $this->replacePlaceHolders($this->_query, $params);
        $res = $this->_dynamicBindResults($stmt);
        $this->reset();

        return $res;
    }

    /**
     * @throws Exception
     */
    public function rawQueryOne ($query, $bindParams = null) {
        $res = $this->rawQuery($query, $bindParams);
        if (is_array($res) && isset($res[0]))
            return $res[0];
        return null;
    }

    /**
     * @param $query
     * @param $bindParams
     * @return array|mixed|string|null
     * @throws Exception
     */
    public function rawQueryValue ($query, $bindParams = null) {
        $res = $this->rawQuery($query, $bindParams);
        if (!$res)
            return null;

        $limit = preg_match('/limit\s+1;?$/i', $query);
        $key   = key($res[0]);

        if (isset($res[0][$key]) && $limit)
            return $res[0][$key];

        $newRes = [];
        for ($i = 0; $i < $this->count; $i++)
            $newRes[] = $res[$i][$key];

        return $newRes;
    }

    /**
     * @param $query
     * @param $numRows
     * @return array|false|string
     * @throws ReflectionException
     */
    public function query($query, $numRows = null) {
        $this->_query = $query;
        $stmt = $this->_buildQuery($numRows);
        $stmt->execute();
        $this->_stmtError = $stmt->error;
        $this->_stmtErrno = $stmt->errno;
        $res = $this->_dynamicBindResults($stmt);
        $this->reset();
        return $res;
    }

    /**
     * @param $options
     * @return $this
     * @throws Exception
     */
    public function setQueryOption ($options): db_helper {
        $allowedOptions = [
            'ALL', 'DISTINCT', 'DISTINCTROW', 'HIGH_PRIORITY', 'STRAIGHT_JOIN', 'SQL_SMALL_RESULT',
            'SQL_BIG_RESULT', 'SQL_BUFFER_RESULT', 'SQL_CACHE', 'SQL_NO_CACHE', 'SQL_CALC_FOUND_ROWS',
            'LOW_PRIORITY', 'IGNORE', 'QUICK', 'MYSQLI_NESTJOIN', 'FOR UPDATE', 'LOCK IN SHARE MODE'
        ];

        if (!is_array($options))
            $options = [$options];

        foreach ($options as $option) {
            $option = strtoupper($option);
            if (!in_array($option, $allowedOptions))
                throw new Exception("Wrong query option: $option");

            if ($option == 'MYSQLI_NESTJOIN')
                $this->_nestJoin = true;
            elseif ($option == 'FOR UPDATE')
                $this->_forUpdate = true;
            elseif ($option == 'LOCK IN SHARE MODE')
                $this->_lockInShareMode = true;
            else
                $this->_queryOptions[] = $option;
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function withTotalCount ( ): db_helper {
        $this->setQueryOption('SQL_CALC_FOUND_ROWS');
        return $this;
    }

    /**
     * @param $tableName
     * @param $numRows
     * @param string|array $columns
     * @return $this|array|false|string
     * @throws ReflectionException
     */
    public function get ($tableName, $numRows = null, $columns = '*') {
        if (empty($columns))
            $columns = '*';

        $column = is_array($columns) ? implode(', ', $columns) : $columns;

        if (strpos($tableName, '.') === false)
            $this->_tableName = $this->prefix . $tableName;
        else
            $this->_tableName = $tableName;

        $this->_query = 'SELECT ' . implode(' ', $this->_queryOptions) . ' ' . $column . " FROM " . $this->_tableName;
        $stmt = $this->_buildQuery($numRows);

        if ($this->isSubQuery)
            return $this;

        $stmt->execute();
        $this->_stmtError = $stmt->error;
        $this->_stmtErrno = $stmt->errno;
        $res = $this->_dynamicBindResults($stmt);
        $this->reset();

        return $res;
    }

    /**
     * @param $tableName
     * @param string|array $columns
     * @return mixed
     * @throws ReflectionException
     */
    public function getOne ($tableName, $columns = '*') {
        $res = $this->get($tableName, 1, $columns);

        if ($res instanceof db_helper)
            return $res;
        elseif (is_array($res) && isset($res[0]))
            return $res[0];
        elseif ($res)
            return $res;

        return null;
    }

    /**
     * @param $tableName
     * @param $column
     * @param int $limit
     * @return array|mixed|string|null
     * @throws ReflectionException
     */
    public function getValue ($tableName, $column, int $limit = 1) {
        $res = $this->ArrayBuilder()->get($tableName, $limit, "{$column} AS retval");

        if (!$res)
            return null;

        if ($limit == 1) {
            if (isset($res[0]["retval"]))
                return $res[0]["retval"];
            return null;
        }

        $newRes = [];
        for ($i = 0; $i < $this->count; $i++)
            $newRes[] = $res[$i]['retval'];

        return $newRes;
    }

    /**
     * @param $tableName
     * @param $insertData
     * @return bool|int
     * @throws Exception
     */
    public function insert ($tableName, $insertData) {
        return $this->_buildInsert($tableName, $insertData, 'INSERT');
    }

    /**
     * @param $tableName
     * @param $insertData
     * @return bool|null
     * @throws Exception
     */
    public function replace ($tableName, $insertData): ?bool {
        return $this->_buildInsert($tableName, $insertData, 'REPLACE');
    }

    /**
     * @param $tableName
     * @param $tableData
     * @param $numRows
     * @return bool
     * @throws Exception
     */
    public function update ($tableName, $tableData, $numRows = null): bool {
        if ($this->isSubQuery) return false;

        $this->_query = "UPDATE " . $this->prefix . $tableName;

        $stmt = $this->_buildQuery($numRows, $tableData);
        $status = $stmt->execute();
        $this->reset();
        $this->_stmtError = $stmt->error;
        $this->_stmtErrno = $stmt->errno;
        $this->count = $stmt->affected_rows;

        return $status;
    }

    /**
     * @param $tableName
     * @param $numRows
     * @return bool|void
     * @throws Exception
     */
    public function delete ($tableName, $numRows = null) {
        if ($this->isSubQuery) return;

        $table = $this->prefix . $tableName;

        if (count($this->_join))
            $this->_query = "DELETE " . preg_replace('/.* (.*)/', '$1', $table) . " FROM " . $table;
        else
            $this->_query = "DELETE FROM " . $table;

        $stmt = $this->_buildQuery($numRows);
        $stmt->execute();
        $this->_stmtError = $stmt->error;
        $this->_stmtErrno = $stmt->errno;
        $this->count = $stmt->affected_rows;
        $this->reset();

        return ($stmt->affected_rows > -1); // -1 indicates that the query returned an error
    }

    /**
     * @param $whereProp
     * @param string|int $whereValue
     * @param string $operator
     * @param string $cond
     * @return $this
     */
    public function where ($whereProp, $whereValue = 'DBNULL', string $operator = '=', string $cond = 'AND'): db_helper {
        if (is_array($whereValue) && isset($whereValue[1])) {
            $cond       = $whereValue[2] ?? $cond;
            $operator   = $whereValue[1];
            $whereValue = $whereValue[0] ?? $whereValue;
        }

        if (count($this->_where) == 0)
            $cond = '';

        $this->_where[] = [$cond, $whereProp, $operator, $whereValue];

        return $this;
    }

    /**
     * @param $updateColumns
     * @param $lastInsertId
     * @return $this
     */
    public function onDuplicate ($updateColumns, $lastInsertId = null): db_helper {
        $this->_lastInsertId  = $lastInsertId;
        $this->_updateColumns = $updateColumns;
        return $this;
    }

    /**
     * @param $whereProp
     * @param string|int $whereValue
     * @param $operator
     * @return $this
     */
    public function orWhere ($whereProp, $whereValue = 'DBNULL', $operator = '='): db_helper {
        return $this->where($whereProp, $whereValue, $operator, 'OR');
    }

    /**
     * @param $havingProp
     * @param string|int $havingValue
     * @param string $operator
     * @param string $cond
     * @return $this
     */
    public function having ($havingProp, $havingValue = 'DBNULL', string $operator = '=', string $cond = 'AND'): db_helper {
        if (is_array($havingValue) && ($key = key($havingValue)) != "0") {
            $operator = $key;
            $havingValue = $havingValue[$key];
        }

        if (count($this->_having) == 0)
            $cond = '';

        $this->_having[] = [$cond, $havingProp, $operator, $havingValue];
        return $this;
    }

    /**
     * @param $havingProp
     * @param $havingValue
     * @param $operator
     * @return $this
     */
    public function orHaving ($havingProp, $havingValue = null, $operator = null): db_helper {
        return $this->having($havingProp, $havingValue, $operator, 'OR');
    }

    /**
     * @param $joinTable
     * @param $joinCondition
     * @param string $joinType
     * @return $this
     * @throws Exception
     */
    public function join ($joinTable, $joinCondition, string $joinType = ''): db_helper {
        $allowedTypes = ['LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER', 'NATURAL'];
        $joinType = strtoupper(trim($joinType));

        if ($joinType && !in_array($joinType, $allowedTypes))
            throw new Exception('Wrong JOIN type: ' . $joinType);

        if (!is_object($joinTable))
            $joinTable = $this->prefix . $joinTable;

        $this->_join[] = [$joinType, $joinTable, $joinCondition];

        return $this;
    }

    /**
     * @param $byField
     * @param string $direction
     * @param $fields
     * @return $this
     * @throws Exception
     */
    public function orderBy ($byField, string $direction = "DESC", $fields = null): db_helper {
        $allowedDirection = ["ASC", "DESC"];
        $direction = strtoupper(trim($direction));
        $byField = preg_replace("/[^ -a-z0-9\.\(\),_`\*\'\"]+/i", '', $byField);
        $byField = preg_replace('/(\`)([`a-zA-Z0-9_]*\.)/', '\1' . $this->prefix . '\2', $byField);

        if (empty($direction) || !in_array($direction, $allowedDirection))
            throw new Exception('Wrong order direction: ' . $direction);

        if (is_array($fields)) {
            foreach ($fields as $key => $value)
                $fields[$key] = preg_replace("/[^\x80-\xff-a-z0-9\.\(\),_` ]+/i", '', $value);
            $byField = 'FIELD (' . $byField . ', "' . implode('","', $fields) . '")';
        } elseif (is_string($fields)) {
            $byField = $byField . " REGEXP '" . $fields . "'";
        } elseif ($fields !== null)
            throw new Exception('Wrong custom field or Regular Expression: ' . $fields);

        $this->_orderBy[$byField] = $direction;
        return $this;
    }

    /**
     * @param $byField
     * @return $this
     */
    public function groupBy ($byField): db_helper {
        $byField = preg_replace("/[^-a-z0-9\.\(\),_\* <>=!]+/i", '', $byField);
        $this->_groupBy[] = $byField;
        return $this;
    }

    /**
     * @param $method
     * @return $this
     * @throws Exception
     */
    public function setLockMethod ($method): db_helper {
        // Switch the uppercase string
        switch (strtoupper($method)) {
            // Is it READ or WRITE?
            case "READ" || "WRITE":
                // Succeed
                $this->_tableLockMethod = $method;
                break;
            default:
                // Else throw an exception
                throw new Exception("Bad lock type: Can be either READ or WRITE");
        }
        return $this;
    }

    /**
     * @param $table
     * @return bool
     * @throws ReflectionException
     * @throws Exception
     */
    public function lock ($table): bool {
        // Main Query
        $this->_query = "LOCK TABLES";

        // Is the table an array?
        if (gettype($table) == "array") {
            // Loop trough it and attach it to the query
            foreach ($table as $key => $value) {
                if (gettype($value) == "string") {
                    if ($key > 0)
                        $this->_query .= ",";
                    $this->_query .= " " . $this->prefix . $value . " " . $this->_tableLockMethod;
                }
            }
        } else {
            // Build the table prefix
            $table = $this->prefix . $table;

            // Build the query
            $this->_query = "LOCK TABLES " . $table . " " . $this->_tableLockMethod;
        }

        // Execute the query unprepared because LOCK only works with unprepared statements.
        $result = $this->queryUnprepared($this->_query);
        $errno = $this->mysqli()->errno;

        // Reset the query
        $this->reset();

        // Are there rows modified?
        if ($result) {
            // Return true
            // We can't return ourself because if one table gets locked, all other ones get unlocked!
            return true;
        } else // Something went wrong
            throw new Exception("Locking of table " . $table . " failed", $errno);
    }

    /**
     * @return $this
     * @throws ReflectionException
     * @throws Exception
     */
    public function unlock ( ): db_helper {
        // Build the query
        $this->_query = "UNLOCK TABLES";

        // Execute the query unprepared because UNLOCK and LOCK only works with unprepared statements.
        $result = $this->queryUnprepared($this->_query);
        $errno  = $this->mysqli()->errno;

        // Reset the query
        $this->reset();

        // Are there rows modified?
        if ($result)
            return $this;
        else // Something went wrong
            throw new Exception("Unlocking of tables failed", $errno);
    }

    /**
     * @return mixed
     * @throws ReflectionException
     */
    public function getInsertId ( ) {
        return $this->mysqli()->insert_id;
    }

    /**
     * @param $str
     * @return mixed
     * @throws ReflectionException
     */
    public function escape ($str) {
        return $this->mysqli()->real_escape_string($str);
    }

    /**
     * @return mixed
     * @throws ReflectionException
     */
    public function ping ( ) {
        return $this->mysqli()->ping();
    }

    /**
     * @param $item
     * @return string
     */
    protected function _determineType ($item): string {
        switch (gettype($item)) {
            case 'NULL':
            case 'string':
                return 's';

            case 'boolean':
            case 'integer':
                return 'i';

            case 'blob':
                return 'b';

            case 'double':
                return 'd';
        }
        return '';
    }

    /**
     * @param $value
     * @return void
     */
    protected function _bindParam ($value) {
        $this->_bindParams[0].= $this->_determineType($value);
        $this->_bindParams[]  = $value;
    }

    /**
     * @param $values
     * @return void
     */
    protected function _bindParams ($values) {
        foreach ($values as $value)
            $this->_bindParam($value);
    }

    /**
     * @param $operator
     * @param $value
     * @return string
     */
    protected function _buildPair ($operator, $value): string {
        if (!is_object($value)) {
            $this->_bindParam($value);
            return ' ' . $operator . ' ? ';
        }

        $subQuery = $value->getSubQuery();
        $this->_bindParams($subQuery['params']);

        return " " . $operator . " (" . $subQuery['query'] . ") " . $subQuery['alias'];
    }

    /**
     * @param $tableName
     * @param $insertData
     * @param $operation
     * @return bool|int|void
     * @throws Exception
     */
    private function _buildInsert ($tableName, $insertData, $operation) {
        if ($this->isSubQuery) return;

        $this->_query = $operation . " " . implode(' ', $this->_queryOptions) . " INTO " . $this->prefix . $tableName;
        $stmt = $this->_buildQuery(null, $insertData);
        $status = $stmt->execute();
        $this->_stmtError = $stmt->error;
        $this->_stmtErrno = $stmt->errno;
        $haveOnDuplicate = !empty ($this->_updateColumns);
        $this->reset();
        $this->count = $stmt->affected_rows;

        if ($stmt->affected_rows < 1) {
            // in case of onDuplicate() usage, if no rows were inserted
            if ($status && $haveOnDuplicate)
                return true;
            return false;
        }

        if ($stmt->insert_id > 0)
            return (int) $stmt->insert_id;

        return true;
    }

    /**
     * @param $numRows
     * @param $tableData
     * @return mixed|void
     * @throws Exception
     */
    protected function _buildQuery ($numRows = null, $tableData = null) {
        // $this->_buildJoinOld();
        $this->_buildJoin();
        $this->_buildInsertQuery($tableData);
        $this->_buildCondition('WHERE', $this->_where);
        $this->_buildGroupBy();
        $this->_buildCondition('HAVING', $this->_having);
        $this->_buildOrderBy();
        $this->_buildLimit($numRows);
        $this->_buildOnDuplicate($tableData);

        if ($this->_forUpdate)
            $this->_query .= ' FOR UPDATE';

        if ($this->_lockInShareMode)
            $this->_query .= ' LOCK IN SHARE MODE';

        $this->_query     = str_replace("%prefix%", $this->prefix, $this->_query);
        $this->_lastQuery = $this->replacePlaceHolders($this->_query, $this->_bindParams);

        if ($this->isSubQuery)
            return;

        // Prepare query
        $stmt = $this->_prepareQuery();

        // Bind parameters to statement if any
        if (count($this->_bindParams) > 1) call_user_func_array([
            $stmt, 'bind_param'
        ], $this->refValues($this->_bindParams));

        return $stmt;
    }

    /**
     * @param mysqli_stmt $stmt
     * @return array|false|string
     * @throws ReflectionException
     */
    protected function _dynamicBindResults (mysqli_stmt $stmt) {
        $parameters = [];
        $results    = [];
        $mysqlLongType = 252;
        $shouldStoreResult = false;

        $meta = $stmt->result_metadata();

        // if $meta is false yet sqlstate is true, there's no sql error but the query is
        // most likely an update/insert/delete which doesn't produce any results
        if (!$meta && $stmt->sqlstate)
            return [];

        $row = [];
        while ($field = $meta->fetch_field()) {
            if ($field->type == $mysqlLongType)
                $shouldStoreResult = true;

            if ($this->_nestJoin && $field->table != $this->_tableName) {
                $field->table = substr($field->table, strlen($this->prefix));
                $row[$field->table][$field->name] = null;
                $parameters[] = &$row[$field->table][$field->name];
            } else {
                $row[$field->name] = null;
                $parameters[] = &$row[$field->name];
            }
        }

        if ($shouldStoreResult)
            $stmt->store_result();

        call_user_func_array([$stmt, 'bind_result'], $parameters);

        $this->totalCount = 0;
        $this->count = 0;

        while ($stmt->fetch()) {
            if ($this->returnType == 'object') {
                $result = new stdClass ();
                foreach ($row as $key => $val) {
                    if (is_array($val)) {
                        $result->$key = new stdClass ();
                        foreach ($val as $k => $v)
                            $result->$key->$k = $v;
                    } else
                        $result->$key = $val;
                }
            } else {
                $result = [];
                foreach ($row as $key => $val) {
                    if (is_array($val))
                        foreach ($val as $k => $v)
                            $result[$key][$k] = $v;
                    else
                        $result[$key] = $val;
                }
            }
            $this->count++;
            if ($this->_mapKey)
                $results[$row[$this->_mapKey]] = count($row) > 2 ? $result : end($result);
            else
                $results[] = $result;
        }

        if ($shouldStoreResult)
            $stmt->free_result();

        $stmt->close();

        // stored procedures sometimes can return more then 1 result set
        if ($this->mysqli()->more_results())
            $this->mysqli()->next_result();

        if (in_array('SQL_CALC_FOUND_ROWS', $this->_queryOptions)) {
            $stmt = $this->mysqli()->query('SELECT FOUND_ROWS()');
            $totalCount = $stmt->fetch_row();
            $this->totalCount = $totalCount[0];
        }

        if ($this->returnType == 'json')
            return json_encode($results);

        return $results;
    }

    /**
     * @return void
     */
    protected function _buildJoinOld ( ) {
        if (empty($this->_join))
            return;

        foreach ($this->_join as $data) {
            list ($joinType, $joinTable, $joinCondition) = $data;

            if (is_object($joinTable))
                $joinStr = $this->_buildPair("", $joinTable);
            else
                $joinStr = $joinTable;

            $this->_query .= " " . $joinType . " JOIN " . $joinStr .
                (false !== stripos($joinCondition, 'using') ? " " : " on ")
                . $joinCondition;
        }
    }

    /**
     * @param $tableData
     * @param $tableColumns
     * @param $isInsert
     * @return void
     * @throws Exception
     */
    public function _buildDataPairs ($tableData, $tableColumns, $isInsert) {
        foreach ($tableColumns as $column) {
            $value = $tableData[$column];

            if (!$isInsert) {
                if (strpos($column, '.') === false)
                    $this->_query .= "`" . $column . "` = ";
                else
                    $this->_query .= str_replace('.', '.`', $column) . "` = ";
            }

            // Subquery value
            if ($value instanceof db_helper) {
                $this->_query .= $this->_buildPair("", $value) . ", ";
                continue;
            }

            // Simple value
            if (!is_array($value)) {
                $this->_bindParam($value);
                $this->_query .= '?, ';
                continue;
            }

            // Function value
            $key = key($value);
            $val = $value[$key];
            switch ($key) {
                case '[I]':
                    $this->_query .= $column . $val . ", ";
                    break;
                case '[F]':
                    $this->_query .= $val[0] . ", ";
                    if (!empty($val[1]))
                        $this->_bindParams($val[1]);
                    break;
                case '[N]':
                    if ($val == null)
                        $this->_query .= "!" . $column . ", ";
                    else
                        $this->_query .= "!" . $val . ", ";
                    break;
                default:
                    throw new Exception("Wrong operation");
            }
        }
        $this->_query = rtrim($this->_query, ', ');
    }

    /**
     * @param $tableData
     * @return void
     * @throws Exception
     */
    protected function _buildOnDuplicate ($tableData) {
        if (is_array($this->_updateColumns) && !empty($this->_updateColumns)) {
            $this->_query .= " ON DUPLICATE KEY UPDATE ";
            if ($this->_lastInsertId)
                $this->_query .= $this->_lastInsertId . "=LAST_INSERT_ID (" . $this->_lastInsertId . "), ";

            foreach ($this->_updateColumns as $key => $val) {
                // skip all params without a value
                if (is_numeric($key)) {
                    $this->_updateColumns[$val] = '';
                    unset($this->_updateColumns[$key]);
                } else
                    $tableData[$key] = $val;
            }
            $this->_buildDataPairs($tableData, array_keys($this->_updateColumns), false);
        }
    }

    /**
     * @param $tableData
     * @return void
     * @throws Exception
     */
    protected function _buildInsertQuery ($tableData) {
        if (!is_array($tableData)) return;

        $isInsert = preg_match('/^[INSERT|REPLACE]/', $this->_query);
        $dataColumns = array_keys($tableData);
        if ($isInsert) {
            if (isset ($dataColumns[0]))
                $this->_query .= ' (`' . implode('`, `', $dataColumns) . '`) ';
            $this->_query .= ' VALUES (';
        } else
            $this->_query .= " SET ";

        $this->_buildDataPairs($tableData, $dataColumns, $isInsert);

        if ($isInsert)
            $this->_query .= ')';
    }

    /**
     * @param $operator
     * @param $conditions
     * @return void
     */
    protected function _buildCondition ($operator, &$conditions) {
        if (empty($conditions)) return;

        //Prepare the where portion of the query
        $this->_query .= ' ' . $operator;

        foreach ($conditions as $cond) {
            list ($concat, $varName, $operator, $val) = $cond;
            $this->_query .= " " . $concat . " " . $varName;

            switch (strtolower($operator)) {
                case 'not in':
                case 'in':
                    $comparison = ' ' . $operator . ' (';
                    if (is_object($val))
                        $comparison .= $this->_buildPair("", $val);
                    else {
                        foreach ($val as $v) {
                            $comparison .= ' ?,';
                            $this->_bindParam($v);
                        }
                    }
                    $this->_query .= rtrim($comparison, ',') . ' ) ';
                    break;
                case 'not between':
                case 'between':
                    $this->_query .= " $operator ? AND ? ";
                    $this->_bindParams($val);
                    break;
                case 'not exists':
                case 'exists':
                    $this->_query .= $operator . $this->_buildPair("", $val);
                    break;
                default:
                    if (is_array($val))
                        $this->_bindParams($val);
                    elseif ($val === null)
                        $this->_query .= ' ' . $operator . " NULL";
                    elseif ($val != 'DBNULL' || $val == '0')
                        $this->_query .= $this->_buildPair($operator, $val);
            }
        }
    }

    /**
     * @return void
     */
    protected function _buildGroupBy ( ) {
        if (empty($this->_groupBy))
            return;

        $this->_query .= " GROUP BY ";

        foreach ($this->_groupBy as $key => $value)
            $this->_query .= $value . ", ";

        $this->_query = rtrim($this->_query, ', ') . " ";
    }

    /**
     * @return void
     */
    protected function _buildOrderBy ( ) {
        if (empty($this->_orderBy))
            return;

        $this->_query .= " ORDER BY ";
        foreach ($this->_orderBy as $prop => $value) {
            if (strtolower(str_replace(" ", "", $prop)) == 'rand()')
                $this->_query .= "rand(), ";
            else
                $this->_query .= $prop . " " . $value . ", ";
        }

        $this->_query = rtrim($this->_query, ', ') . " ";
    }

    /**
     * @param $numRows
     * @return void
     */
    protected function _buildLimit ($numRows) {
        if (!isset($numRows))
            return;

        if (is_array($numRows))
            $this->_query .= ' LIMIT ' . ((int)($numRows[0] > -1 ? $numRows[0] : 0)) . ', ' . (int)$numRows[1];
        else
            $this->_query .= ' LIMIT ' . (int)$numRows;
    }

    /**
     * @return mixed
     * @throws ReflectionException
     * @throws Exception
     */
    protected function _prepareQuery ( ) {
        $stmt = $this->mysqli()->prepare(str_replace("%prefix%", $this->prefix, $this->_query));

        if ($stmt !== false) {
            if ($this->traceEnabled)
                $this->traceStartQ = microtime(true);
            return $stmt;
        }

        if ($this->mysqli()->errno === 2006 && $this->autoReconnect === true && $this->autoReconnectCount === 0) {
            $this->connect($this->defConnectionName);
            $this->autoReconnectCount++;
            return $this->_prepareQuery();
        }

        $error = $this->mysqli()->error;
        $query = $this->_query;
        $errno = $this->mysqli()->errno;
        $this->reset();
        throw new Exception(sprintf('%s query: %s', $error, $query), $errno);
    }

    /**
     * @param array $arr
     * @return array
     */
    protected function refValues (array &$arr): array {
        if (strnatcmp(phpversion(), '5.3') >= 0) {
            $refs = [];
            foreach ($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs;
        }
        return $arr;
    }

    /**
     * @param $str
     * @param $vals
     * @return mixed|string
     */
    protected function replacePlaceHolders ($str, $vals) {
        $i = 1;
        $newStr = "";

        if (empty($vals))
            return $str;

        while ($pos = strpos($str, "?")) {
            $val = $vals[$i++];

            if (is_object($val))
                $val = '[object]';

            if ($val === null)
                $val = 'NULL';

            $newStr .= substr($str, 0, $pos) . "'" . $val . "'";
            $str = substr($str, $pos + 1);
        }

        $newStr .= $str;
        return $newStr;
    }

    /**
     * @return string|null
     */
    public function getLastQuery ( ): ?string {
        return $this->_lastQuery;
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    public function getLastError ( ): string {
        if (!isset($this->_mysqli[$this->defConnectionName]))
            return "mysqli is null";
        return trim($this->_stmtError . " " . $this->mysqli()->error);
    }

    /**
     * @return mixed
     */
    public function getLastErrno ( ) {
        return $this->_stmtErrno;
    }

    /**
     * @return array|null
     */
    public function getSubQuery ( ): ?array {
        if (!$this->isSubQuery)
            return null;

        array_shift($this->_bindParams);
        $val = [
            'query'  => $this->_query,
            'params' => $this->_bindParams,
            'alias'  => isset($this->connectionsSettings[$this->defConnectionName]) ? $this->connectionsSettings[$this->defConnectionName]['host'] : null
        ];

        $this->reset();
        return $val;
    }

    /**
     * @param string $subQueryAlias
     * @return db_helper
     */
    public static function subQuery (string $subQueryAlias = ""): db_helper {
        return new self([
            'host' => $subQueryAlias,
            'isSubQuery' => true
        ]);
    }

    /**
     * @return mixed
     */
    public function copy ( ) {
        $copy = unserialize(serialize($this));
        $copy->_mysqli = [];
        return $copy;
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function startTransaction ( ) {
        $this->mysqli()->autocommit(false);
        $this->_transaction_in_progress = true;
        register_shutdown_function([$this, "_transaction_status_check"]);
    }

    /**
     * @return mixed
     * @throws ReflectionException
     */
    public function commit ( ) {
        $result = $this->mysqli()->commit();
        $this->_transaction_in_progress = false;
        $this->mysqli()->autocommit(true);
        return $result;
    }

    /**
     * @return mixed
     * @throws ReflectionException
     */
    public function rollback ( ) {
        $result = $this->mysqli()->rollback();
        $this->_transaction_in_progress = false;
        $this->mysqli()->autocommit(true);
        return $result;
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function _transaction_status_check ( ) {
        if (!$this->_transaction_in_progress)
            return;
        $this->rollback();
    }

    /**
     * @param $enabled
     * @param $stripPrefix
     * @return $this
     */
    public function setTrace ($enabled, $stripPrefix = null): db_helper {
        $this->traceEnabled = $enabled;
        $this->traceStripPrefix = $stripPrefix;
        return $this;
    }

    /**
     * @return string
     */
    private function _traceGetCaller ( ): string {
        $dd = debug_backtrace();
        $caller = next($dd);

        while (isset($caller) && $caller["file"] == __FILE__)
            $caller = next($dd);

        return __CLASS__ . "->" . $caller["function"] . "() >>  file \"" .
            str_replace($this->traceStripPrefix, '', $caller["file"]) . "\" line #" . $caller["line"] . " ";
    }

    /**
     * @param $tables
     * @return bool
     * @throws ReflectionException
     */
    public function tableExists ($tables): bool {
        $tables = !is_array($tables) ? [$tables] : $tables;
        $count  = count($tables);

        if ($count == 0)
            return false;

        foreach ($tables as $i => $value)
            $tables[$i] = $this->prefix . $value;

        $db = isset($this->connectionsSettings[$this->defConnectionName]) ? $this->connectionsSettings[$this->defConnectionName]['db\db'] : null;
        $this->where('table_schema', $db);
        $this->where('table_name', $tables, 'in');
        $this->get('information_schema.tables', $count);

        return $this->count == $count;
    }

    /**
     * @param $idField
     * @return $this
     */
    public function map ($idField): db_helper {
        $this->_mapKey = $idField;
        return $this;
    }

    /**
     * @param $table
     * @param $page
     * @param $fields
     * @return $this|array|false|string
     * @throws Exception
     */
    public function paginate ($table, $page, $fields = null) {
        $offset = $this->pageLimit * ($page - 1);
        $res = $this->withTotalCount()->get($table, [$offset, $this->pageLimit], $fields);
        $this->totalPages = (int) ceil($this->totalCount / $this->pageLimit);
        return $res;
    }

    /**
     * @param $whereJoin
     * @param $whereProp
     * @param string|int $whereValue
     * @param string $operator
     * @param string $cond
     * @return $this
     */
    public function joinWhere ($whereJoin, $whereProp, $whereValue = 'DBNULL', string $operator = '=', string $cond = 'AND'): db_helper {
        $this->_joinAnd[$this->prefix . $whereJoin][] = [$cond, $whereProp, $operator, $whereValue];
        return $this;
    }

    /**
     * @param $whereJoin
     * @param $whereProp
     * @param string|int $whereValue
     * @param string $operator
     * @return $this
     */
    public function joinOrWhere ($whereJoin, $whereProp, $whereValue = 'DBNULL', string $operator = '='): db_helper {
        return $this->joinWhere($whereJoin, $whereProp, $whereValue, $operator, 'OR');
    }

    /**
     * @return void
     */
    protected function _buildJoin ( ) {
        if (empty ($this->_join))
            return;

        foreach ($this->_join as $data) {
            list ($joinType, $joinTable, $joinCondition) = $data;

            if (is_object($joinTable))
                $joinStr = $this->_buildPair("", $joinTable);
            else
                $joinStr = $joinTable;

            $this->_query .= " " . $joinType . " JOIN " . $joinStr .
                (false !== stripos($joinCondition, 'using') ? " " : " on ")
                . $joinCondition;

            // Add join and query
            if (!empty($this->_joinAnd) && isset($this->_joinAnd[$joinStr])) {
                foreach ($this->_joinAnd[$joinStr] as $join_and_cond) {
                    list ($concat, $varName, $operator, $val) = $join_and_cond;
                    $this->_query .= " " . $concat . " " . $varName;
                    $this->conditionToSql($operator, $val);
                }
            }
        }
    }

    /**
     * @param $operator
     * @param $val
     * @return void
     */
    private function conditionToSql ($operator, $val) {
        switch (strtolower($operator)) {
            case 'not in':
            case 'in':
                $comparison = ' ' . $operator . ' (';
                if (is_object($val)) {
                    $comparison .= $this->_buildPair("", $val);
                } else {
                    foreach ($val as $v) {
                        $comparison .= ' ?,';
                        $this->_bindParam($v);
                    }
                }
                $this->_query .= rtrim($comparison, ',') . ' ) ';
                break;
            case 'not between':
            case 'between':
                $this->_query .= " $operator ? AND ? ";
                $this->_bindParams($val);
                break;
            case 'not exists':
            case 'exists':
                $this->_query .= $operator . $this->_buildPair("", $val);
                break;
            default:
                if (is_array($val))
                    $this->_bindParams($val);
                else if ($val === null)
                    $this->_query .= $operator . " NULL";
                else if ($val != 'DBNULL' || $val == '0')
                    $this->_query .= $this->_buildPair($operator, $val);
        }
    }

    /**
     * @param $string
     * @return void
     */
    public function error ($string) {
        ob_clean();
        die("<div style='width: 80%;height: 80px;background: whitesmoke;border-radius: 0 8px 8px 0;font-family: system-ui;font-weight: bolder;display: flex;align-items: center;padding: 0 10px;box-sizing: border-box;border-left: 5px solid red;'><span>ERROR</span>&nbsp;-&nbsp;$string</div><style>body{display:flex;justify-content: center}</style>");
    }

}