<?php

$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","./mail.php?folder=inbox");
$tpl->setVariable("BTN_TXT", $lng->txt("inbox"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK", "mail_new.php");
$tpl->setVariable("BTN_TXT", $lng->txt("compose"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK", "mail_options.php");
$tpl->setVariable("BTN_TXT", $lng->txt("options"));
$tpl->parseCurrentBlock();

$tpl->touchBlock("btn_row");

?>