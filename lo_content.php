<?php
/**
* lessons
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "./classes/class.Explorer.php";
require_once "./include/inc.sort.php";

// TODO: this function is common and belongs to class util!
/**
* builds a string to show the context
*
* @param int $obj_id
* @param int $parent_id
* @return string
* @access public
*/
function getContextPath($obj_id, $parent_id)
{
	global $tree;		

	$path = "";		
	
	$tmpPath = $tree->getPathFull($obj_id, $parent_id);		
	// count -1, to exclude the forum itself
	for ($i = 0; $i < (count($tmpPath)-1); $i++)
	{
		if ($path != "")
		{
			$path .= " > ";
		}
		
		$path .= $tmpPath[$i]["title"];
	}

	return $path;
}

$tpl->addBlockFile("CONTENT", "content", "tpl.lo_overview.html");

// add everywhere wegen sparkassen skin
$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
$tpl->touchBlock("buttons");

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_PAGEHEADLINE",  $lng->txt("lo_available"));
//$tpl->parseCurrentBlock();			// this line produces an empty <h1></h1>, alex 16.2.03

if (!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == "flat")
{
	$tpl->setCurrentBlock("btn_cell");
	$tpl->setVariable("BTN_LINK","lo.php?viewmode=tree");
	$tpl->setVariable("BTN_TXT", $lng->txt("treeview"));
	$tpl->parseCurrentBlock();
}
else
{
	$tpl->setCurrentBlock("btn_cell");
	$tpl->setVariable("BTN_LINK","lo.php?viewmode=flat");
	$tpl->setVariable("BTN_TARGET","target=\"_parent\"");
	$tpl->setVariable("BTN_TXT", $lng->txt("flatview"));
	$tpl->parseCurrentBlock();
}

// display different content depending on viewmode
switch ($_SESSION["viewmode"])
{
	case "flat":
		$lr_arr = TUtil::getObjectsByOperations('le','visible');
		$lr_arr = TUtil::getObjectsByOperations('crs','visible');
		
		usort($lr_arr,"sortObjectsByTitle");
		
		$lr_num = count($lr_arr);
		
		if ($lr_num > 0)
		{
			// counter for rowcolor change
			$num = 0;
			
			foreach ($lr_arr as $lr_data)
			{
				$tpl->setCurrentBlock("learningstuff_row");
		
				// change row color
				$tpl->setVariable("ROWCOL", TUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;		
		
				$obj_link = "lo_view.php?lm_id=".$lr_data["obj_id"];
				$obj_icon = "icon_".$lr_data["type"].".gif";
		 
				$tpl->setVariable("TITLE", $lr_data["title"]);
				$tpl->setVariable("LO_LINK", $obj_link);
				$tpl->setVariable("IMG", $obj_icon);
				$tpl->setVariable("ALT_IMG", $lr_data["data"]);		
				$tpl->setVariable("DESCRIPTION", $lr_data["description"]);
				$tpl->setVariable("STATUS", "N/A");
				$tpl->setVariable("LAST_VISIT", "N/A");
				$tpl->setVariable("LAST_CHANGE", Format::formatDate($lr_data["last_update"]));
				$tpl->setVariable("CONTEXTPATH", getContextPath($lr_data["obj_id"], $lr_data["parent"]));
				
				$tpl->parseCurrentBlock("learningstuff_row");
			}
		}
		else
		{
			$tpl->setCurrentBlock("no_content");
			$tpl->setVAriable("TXT_MSG_NO_CONTENT",$lng->txt("lo_no_content"));
			$tpl->parseCurrentBlock("no_content");
		}
		
		$tpl->setCurrentBlock("learningstuff");
		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));
		$tpl->setVariable("TXT_STATUS", $lng->txt("status"));
		$tpl->setVariable("TXT_LAST_VISIT", $lng->txt("last_visit"));
		$tpl->setVariable("TXT_LAST_CHANGE", $lng->txt("last_change"));
		$tpl->setVariable("TXT_CONTEXTPATH", $lng->txt("context"));
		$tpl->parseCurrentBlock("learningstuff");
		
		if ($_GET["message"])
		{
		    $tpl->addBlockFile("MESSAGE", "message2", "tpl.message.html");
			$tpl->setCurrentBlock("message2");
			$tpl->setVariable("MSG", urldecode( $_GET["message"]));
			$tpl->parseCurrentBlock();
		}
		break;
		
	case "tree":
//go through valid objects and filter out the lessons only
$lessons = array();
if ($objects = $tree->getChilds($_GET["obj_id"],"title"))
{
	foreach ($objects as $key => $object)
	{
		if ($object["type"] == "le" && $rbacsystem->checkAccess('visible',$object["child"],$object["parent"]))
		{
			$lessons[$key] = $object;
		}
	}
}

//TODO: maybe move the code above to this method
//$lessons = $ilias->account->getLessons();

		$lr_num = count($lessons);
		if ($lr_num > 0)
		{
			// counter for rowcolor change
			$num = 0;
			
			foreach ($lessons as $lr_data)
			{
				$tpl->setCurrentBlock("learningstuff_row");
		
				// change row color
				$tpl->setVariable("ROWCOL", TUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;		
		
				$obj_link = "lo_view.php?lm_id=".$lr_data["obj_id"];
				$obj_icon = "icon_".$lr_data["type"].".gif";
		 
				$tpl->setVariable("TITLE", $lr_data["title"]);
				$tpl->setVariable("LO_LINK", $obj_link);
				$tpl->setVariable("IMG", $obj_icon);
				$tpl->setVariable("ALT_IMG", $lr_data["data"]);		
				$tpl->setVariable("DESCRIPTION", $lr_data["description"]);
				$tpl->setVariable("STATUS", "N/A");
				$tpl->setVariable("LAST_VISIT", "N/A");
				$tpl->setVariable("LAST_CHANGE", Format::formatDate($lr_data["last_update"]));
				$tpl->setVariable("CONTEXTPATH", getContextPath($lr_data["obj_id"], $lr_data["parent"]));
				
				$tpl->parseCurrentBlock("learningstuff_row");
			}
		}
		else
		{
			$tpl->setCurrentBlock("no_content");
			$tpl->setVAriable("TXT_MSG_NO_CONTENT",$lng->txt("lo_no_content"));
			$tpl->parseCurrentBlock("no_content");
		}
		
		$tpl->setCurrentBlock("learningstuff");
		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));
		$tpl->setVariable("TXT_STATUS", $lng->txt("status"));
		$tpl->setVariable("TXT_LAST_VISIT", $lng->txt("last_visit"));
		$tpl->setVariable("TXT_LAST_CHANGE", $lng->txt("last_change"));
		$tpl->setVariable("TXT_CONTEXTPATH", $lng->txt("context"));
		$tpl->parseCurrentBlock("learningstuff");

/*
foreach ($lessons as $row)
{
	$tpl->setCurrentBlock("subcategory");
	$tpl->setVariable("ROWCOL","tblrow".(($j%2)+1));
	$tpl->setVariable("TITLE", $row["title"]);
	$tpl->setVariable("LINK_LO", "lo_view.php?lm_id=".$row["id"]);
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
*/

/*
if ($tpl->includeTree() == true)
{
	if ($_GET["expand"] == "")
	{
		$expanded = "1";
	}
	else
		$expanded = $_GET["expand"];
	
	$tplTree = new Template("tpl.explorer.html",true,true);
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
*/
		break;
}

$tpl->show();
?>