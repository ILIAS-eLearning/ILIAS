<?php

$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","./mail.php?folder=inbox");
$tplbtn->setVariable("BTN_TXT", $lng->txt("inbox"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail_new.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("compose"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail_options.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("options"));
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

/*
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail.php?folder=archive");
$tplbtn->setVariable("BTN_TXT", $lng->txt("archive"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail.php?folder=sent");
$tplbtn->setVariable("BTN_TXT", $lng->txt("sent"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail.php?folder=drafts");
$tplbtn->setVariable("BTN_TXT", $lng->txt("drafts"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail.php?folder=trash");
$tplbtn->setVariable("BTN_TXT", $lng->txt("trash"));
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();
*/
$tpl->setVariable("BUTTONS",$tplbtn->get());


?>