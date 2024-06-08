<?php

/**
 * PHPMailer autoload
 */

require "PHPMailer.php";
require "SMTP.php";
require "Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$PHPMailer = new PHPMailer(true);