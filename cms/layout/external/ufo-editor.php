<?php ob_clean() ?>

<?php $ufo->load_layout("document"); ?>

<div class="ufo-layer-lock db"><div class="ufo-editor-loader flex-direction-column"><div class="ufo-loader-box"><span></span><span></span><span></span></div></div></div>

<header class="flex flex-start ufo-header-editor">

    <div class="right">
        <span class="title"><?=$ufo->lng("Untitled")?></span>
    </div>

    <div class="left flex align-center">
        <div class="ufo-toolbar-wrp"></div>
    </div>

</header>

<div class="ufo-p-editor-layout">

    <div class="main">

        <div class="ufo-content-editor" dir="auto" contenteditable="true" translate="no">
            <div class="ufo-sortable ufo-rich-text"><?=$ufo->lng("Type something...")?></div>
        </div>

        <div class="ufo-element-droppable">
            <div class="ufo-create-child-container">
                <i class="ufo-icon-plus-circle ufo-add-columns"></i>
                <strong><?=$ufo->lng("Drop the widget here")?></strong>
            </div>
        </div>

    </div>

</div>

<?php $ufo->load_layout("endDoc"); ?>