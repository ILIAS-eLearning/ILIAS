<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for sub items listed in repository administration
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesRepository
*/
class ilAdminSubItemsTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->ref_id = $a_ref_id;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
//		$this->setTitle($lng->txt("items"));
		$this->setSelectAllCheckbox("id[]");
		
		$this->addColumn("", "", "1", 1);
		$this->addColumn($this->lng->txt("type"), "", "1");
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("last_change"), "last_update", "25%");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.admin_sub_items_row.html", "Services/Repository");
		//$this->disable("footer");
		$this->setEnableTitle(true);
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");

		// TODO: Needs other solution
		if(ilObject::_lookupType((int) $_GET['ref_id'],true) == 'chac')
		{
			$this->getItems();
			return true;
		}
		

		if (ilObject::_lookupType($_GET["ref_id"], true) != "recf")
		{
			if ($_SESSION["clipboard"])
			{
				$this->addCommandButton("paste", $lng->txt("paste"));
				$this->addCommandButton("clear", $lng->txt("clear"));
			}
			else
			{
				$this->addMultiCommand("cut", $lng->txt("cut"));
				$this->addMultiCommand("delete", $lng->txt("delete"));
				$this->addMultiCommand("link", $lng->txt("link"));
			}
		}
		else
		{
			if ($_SESSION["clipboard"])
			{
				$this->addCommandButton("clear", $lng->txt("clear"));
			}
			else
			{
				$this->addMultiCommand("cut", $lng->txt("cut"));
				$this->addMultiCommand("removeFromSystem", $lng->txt("btn_remove_system"));
			}
		}
		$this->getItems();
	}
	
	/**
	* Get items
	*/
	function getItems()
	{
		global $rbacsystem, $objDefinition, $tree;
		
		$items = array();
		$childs = $tree->getChilds($this->ref_id);
		foreach ($childs as $key => $val)
	    {
			// visible
			if (!$rbacsystem->checkAccess("visible",$val["ref_id"]))
			{
				continue;
			}
			
			// hide object types in devmode
			if ($objDefinition->getDevMode($val["type"]))
			{
				continue;
			}
			
			// don't show administration in root node list
			if ($val["type"] == "adm")
			{
				continue;
			}
			if (!$this->parent_obj->isVisible($val["ref_id"], $val["type"]))
			{
				continue;
			}
			$items[] = $val;
		}
		$this->setData($items);
	}
	
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $lng, $objDefinition, $ilCtrl;

//		$this->tpl->setVariable("", );
		
		// surpress checkbox for particular object types AND the system role
		if (!$objDefinition->hasCheckbox($a_set["type"]) ||
			$a_set["obj_id"] == SYSTEM_ROLE_ID ||
			$a_set["obj_id"] == SYSTEM_USER_ID ||
			$a_set["obj_id"] == ANONYMOUS_ROLE_ID)
		{
			$this->tpl->touchBlock("no_checkbox");
		}
		else
		{
			$this->tpl->setCurrentBlock("checkbox");
			$this->tpl->setVariable("ID", $a_set["ref_id"]);
			$this->tpl->parseCurrentBlock();
		}

		//build link
		$class_name = $objDefinition->getClassName($a_set["type"]);
		$class = strtolower("ilObj".$class_name."GUI");
		$ilCtrl->setParameterByClass($class, "ref_id", $a_set["ref_id"]);
		$this->tpl->setVariable("HREF_TITLE", $ilCtrl->getLinkTargetByClass($class, "view"));
		$ilCtrl->setParameterByClass($class, "ref_id", $_GET["ref_id"]);
					
		// TODO: broken! fix me
		$title = $a_set["title"];
		if (is_array($_SESSION["clipboard"]["ref_ids"]))
		{
			if (in_array($a_set["ref_id"], $_SESSION["clipboard"]["ref_ids"]))
			{
				switch($_SESSION["clipboard"]["cmd"])
				{
					case "cut":
						$title = "<del>".$title."</del>";
						break;
	
					case "copy":
						$title = "<font color=\"green\">+</font>  ".$title;
						break;
							
					case "link":
						$title = "<font color=\"black\"><</font> ".$title;
						break;
				}
			}
		}
		$this->tpl->setVariable("VAL_TITLE", $title);
		$this->tpl->setVariable("VAL_DESC", ilUtil::shortenText($a_set["desc"] ,ilObject::DESC_LENGTH, true));
		$this->tpl->setVariable("VAL_LAST_CHANGE", $a_set["last_update"]);
		$alt = ($objDefinition->isPlugin($a_set["type"]))
			? $lng->txt("icon")." ".ilPlugin::lookupTxt("rep_robj", $a_set["type"], "obj_".$a_set["type"])
			: $lng->txt("icon")." ".$lng->txt("obj_".$a_set["type"]);
		$this->tpl->setVariable("IMG_TYPE", ilUtil::img(ilObject::_getIcon($a_set["obj_id"], "small"), $alt));
		//$this->tpl->setVariable("IMG_TYPE", ilObject::_getIcon($a_set["obj_id"], "small", $this->getIconImageType()),
		//	$lng->txt("icon")." ".$lng->txt("obj_".$a_set["type"])));
	}

}
?>
