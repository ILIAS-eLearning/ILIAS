<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* lessons
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "./classes/class.ilExplorer.php";
require_once "./include/inc.sort.php";

// TODO: this function is common and belongs to class util!
/**
* builds a path string to show the context
* you may leave startnode blank. root node of tree is used instead
* @param	integer	endnode_id
* @param	integer	startnode_id
* @return	string	path
* @access	public
*/
function getContextPath($a_endnode_id, $a_startnode_id = 0)
{
	global $tree;		

	$path = "";		
	
	$tmpPath = $tree->getPathFull($a_endnode_id, $a_startnode_id);		

	// count -1, to exclude the forum itself
	for ($i = 0; $i < (count($tmpPath) - 1); $i++)
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
// display infopanel if something happened
infoPanel();

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
		$lr_arr = ilUtil::getObjectsByOperations('le','visible');
		$lr_arr = ilUtil::getObjectsByOperations('crs','visible');

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
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;

				$obj_link = "lo_view.php?lm_id=".$lr_data["ref_id"];
				$obj_icon = "icon_".$lr_data["type"]."_b.gif";

				$tpl->setVariable("TITLE", $lr_data["title"]);
				$tpl->setVariable("LO_LINK", $obj_link);
				if($lr_data["type"] == "le")		// Test
				{
					$tpl->setVariable("EDIT_LINK","content/lm_edit.php?lm_id=".$lr_data["obj_id"]);
					$tpl->setVariable("TXT_EDIT", "(".$lng->txt("edit").")");
					$tpl->setVariable("VIEW_LINK","content/lm_presentation.php?lm_id=".$lr_data["obj_id"]);
					$tpl->setVariable("TXT_VIEW", "(".$lng->txt("view").")");
				}
				$tpl->setVariable("IMG", $obj_icon);
				$tpl->setVariable("ALT_IMG", $lng->txt("obj_".$lr_data["type"]));
				$tpl->setVariable("DESCRIPTION", $lr_data["description"]);
				$tpl->setVariable("STATUS", "N/A");
				$tpl->setVariable("LAST_VISIT", "N/A");
				$tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($lr_data["last_update"]));
				$tpl->setVariable("CONTEXTPATH", getContextPath($lr_data["ref_id"]));

				$tpl->parseCurrentBlock("learningstuff_row");
			}
		}
		else
		{
			$tpl->setCurrentBlock("no_content");
			$tpl->setVariable("TXT_MSG_NO_CONTENT",$lng->txt("lo_no_content"));
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
		$objects = $tree->getChilds($_GET["ref_id"],"title");

		if (count($objects) > 0)
		{
			foreach ($objects as $key => $object)
			{
				if ($object["type"] == "le" && $rbacsystem->checkAccess('visible',$object["child"]))
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
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;

				$obj_link = "lo_view.php?lm_id=".$lr_data["ref_id"];
				$obj_icon = "icon_".$lr_data["type"]."_b.gif";

				$tpl->setVariable("TITLE", $lr_data["title"]);
				$tpl->setVariable("LO_LINK", $obj_link);

				if($lr_data["type"] == "le")		// Test
				{
					$tpl->setVariable("EDIT_LINK","content/lm_edit.php?lm_id=".$lr_data["obj_id"]);
					$tpl->setVariable("TXT_EDIT", "(".$lng->txt("edit").")");
					$tpl->setVariable("VIEW_LINK","content/lm_presentation.php?lm_id=".$lr_data["obj_id"]);
					$tpl->setVariable("TXT_VIEW", "(".$lng->txt("view").")");
				}
				$tpl->setVariable("IMG", $obj_icon);
				$tpl->setVariable("ALT_IMG", $lng->txt("obj_".$lr_data["type"]));
				$tpl->setVariable("DESCRIPTION", $lr_data["description"]);
				$tpl->setVariable("STATUS", "N/A");
				$tpl->setVariable("LAST_VISIT", "N/A");
				$tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($lr_data["last_update"]));
				$tpl->setVariable("CONTEXTPATH", getContextPath($lr_data["ref_id"]));

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
		break;
}
$tpl->show();
?>
