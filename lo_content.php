<?PHP
/**
* lessons
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/ilias_header.inc";
require_once "classes/class.Explorer.php";

$tpl = new Template("tpl.lessons.html", true, true);

$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","courses.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("courses"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl->setVariable("TXT_PAGEHEADLINE",  $lng->txt("lo_available"));
$tpl->setVariable("BUTTONS",$tplbtn->get());

$lessons = array();

//go through valid objects and filter out the lessons only
if ($objects = $tree->getChilds($_GET["obj_id"],"title"))
{
	foreach ($objects as $key => $object)
	{
		if ($object["type"] == "le" && $rbacsystem->checkAccess('visible',$object["id"],$object["parent"]))
		{
			$lessons[$key] = $object;
		}
	}
}

//TODO: maybe move the code above to this method
//$lessons = $ilias->account->getLessons();

foreach ($lessons as $row)
{
	$tpl->setCurrentBlock("subcategory");
	$tpl->setVariable("ROWCOL","tblrow".(($j%2)+1));
	$tpl->setVariable("TITLE", $row["title"]);
	$tpl->setVariable("LINK_LO", "lo_content.php?id=".$row["id"]);
	$tpl->setVariable("IMG_AND_LINK","img".$j);
	$tpl->parseCurrentBlock();
}
$tpl->setCurrentBlock("subcategory_others");
$tpl->setVariable("TXT_LO_OTHER_LANGS", $lng->txt("lo_other_langs"));
$tpl->parseCurrentBlock();

//language stuff
$tpl->setCurrentBlock("category");
$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_SUBSCRIPTION", $lng->txt("subscription"));
$tpl->parseCurrentBlock();

if ($tpl->includeTree() == true)
{
	if ($_GET["expand"] == "")
	{
		$expanded = "1";
	}
	else
		$expanded = $_GET["expand"];
	
	$tplTree = new Template("explorer.html",true,true);
	$exp = new Explorer("lo_content.php");
	$exp->setExpand($expanded);
	
	//filter object types
	$exp->addFilter("cat");
	$exp->addFilter("grp");
	$exp->addFilter("crs");
	$exp->setFiltered(true);
	$exp->setFrameTarget("");
	//build html-output
	$exp->setOutput(0);
	$output = $exp->getOutput();
	
	$tplTree->setVariable("EXPLORER",$output);
	$tplTree->setVariable("ACTION", "lo_content.php?expand=".$_GET["expand"]);
	
	$tpl->setVariable("TREE", $tplTree->get());
}


$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>