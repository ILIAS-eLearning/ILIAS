<?php

$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

if (!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == "flat")
{
	$tpl->setCurrentBlock("btn_cell");
	$tpl->setVariable("BTN_LINK","mail_frameset.php?viewmode=tree");
	$tpl->setVariable("BTN_TXT", $lng->txt("treeview"));
	$tpl->parseCurrentBlock();
}
else
{
	$tpl->setCurrentBlock("btn_cell");
	$tpl->setVariable("BTN_LINK","mail_frameset.php?viewmode=flat");
	$tpl->setVariable("BTN_TARGET","target=\"_parent\"");
	$tpl->setVariable("BTN_TXT", $lng->txt("flatview"));
	$tpl->parseCurrentBlock();
}
$file_name = basename($_SERVER["SCRIPT_NAME"]);

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK", "mail.php?mobj_id=$_GET[mobj_id]");
$tpl->setVariable("BTN_TXT", $lng->txt("mail"));
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK", "mail_new.php?mobj_id=$_GET[mobj_id]&type=new");
$tpl->setVariable("BTN_TXT", $lng->txt("compose"));
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK", "mail_options.php?mobj_id=$_GET[mobj_id]");
$tpl->setVariable("BTN_TXT", $lng->txt("options"));
$tpl->parseCurrentBlock();
$tpl->touchBlock("btn_row");

?>