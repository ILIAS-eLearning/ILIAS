<?php

$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","lo_edit.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("overview"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "lo_edit.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("information_abbr")."/".$lng->txt("options"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "lo_edit.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("structure"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "lo_edit.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("list_of_pages"));
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "lo_edit.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("glossary"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "lo_edit.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("list_of_questions"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "lo_edit.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("multimedia"));
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl->setVariable("BUTTONS",$tplbtn->get());


?>