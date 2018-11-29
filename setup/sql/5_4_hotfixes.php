<#1>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass("CodeInline", "code_inline", "code",
	array());
?>
<#2>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass("Code", "code_block", "pre",
	array());
?>
<#3>
<?php
$ilDB->update("style_data", array(
	"uptodate" => array("integer", 0)
), array(
	"1" => array("integer", 1)
));
?>