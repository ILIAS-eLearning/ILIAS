<?php
require_once "./include/inc.header.php";
require_once "./classes/class.Search.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.search.html");

$ilias->error_obj->sendInfo("Attention: Search function doesn't work in this release.",$ilias->error_obj->MESSAGE);

if ($_POST["search"] != "")
{
	$mySearch = new Search();
	$mySearch->setArea($_POST["area"]);
	$mySearch->setText($_POST["searchtext"]);
	$mySearch->setOptions($_POST["options"]);

	$tpl->setVariable("SEARCHTEXT", $_POST["searchtext"]);

	//perform search
	if ($mySearch->execute() == true)
	{
		$tpl->setCurrentBlock("message");

		if ($mySearch->hits == 1)
			$msg = "1 ".$lng->txt("hit");
		else
			$msg = $mySearch->hits." ".$lng->txt("hits");
		
		$tpl->setVariable("MSG", $msg);
		$tpl->parseCurrentBlock();

		foreach ($mySearch->result as $row)
		{
		 	$i++;
			$tpl->setCurrentBlock("resultrow");
			$tpl->setVariable("ROWCOL", "tblrow".(($i%2)+1));
			$tpl->setVariable("LINK", $row["link"]);
			$tpl->setVariable("TEXT", $row["text"]);
			$tpl->parseCurrentBlock();
		}
		$tpl->touchBlock("result");
	}
	else
	{
		$tpl->setCurrentBlock("message");
		$tpl->setVariable("MSG", $lng->txt("msg_nothing_found"));
		$tpl->parseCurrentBlock();
	}
}

//fill out select box with search options
$tpl->setCurrentBlock("searcharea");
$tpl->setVariable("SELVALUE", "le");
$tpl->setVariable("SELOPTION", $lng->txt("los"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("searcharea");
$tpl->setVariable("SELVALUE", "usr");
$tpl->setVariable("SELOPTION", $lng->txt("users"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("searcharea");
$tpl->setVariable("SELVALUE", "grp");
$tpl->setVariable("SELOPTION", $lng->txt("groups"));
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_SEARCH", $lng->txt("search"));

$tpl->setVariable("TXT_SEARCH_IN", $lng->txt("search_in"));
$tpl->setVariable("TXT_KEYWORDS",$lng->txt("keywords"));
$tpl->setVariable("TXT_PHRASE", $lng->txt("phrase"));

$tpl->setVariable("TXT_SEARCH", $lng->txt("search"));
$tpl->parseCurrentBlock();

$tpl->show();
?>