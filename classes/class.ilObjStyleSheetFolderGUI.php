<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Class ilObjStyleSheetFolderGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjStyleSheetFolderGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjStyleSheetFolderGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		$this->type = "styf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
	}
	
	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

		// put here object specific stuff
			
		// always send a message
		sendInfo($this->lng->txt("object_added"),true);
		
		ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"")));
	}
	
	/**
	* view list of styles
	*/
	function viewObject()
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		include_once "./classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.styf_row.html");

		$num = 0;

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->object->getTitle(),"icon_styf.gif",
			$this->lng->txt("obj_".$this->object->getType()));

		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		// title
		$header_names = array("", $this->lng->txt("title"));
		$tbl->setHeaderNames($header_names);

		$header_params = array("ref_id" => $this->ref_id);
		$tbl->setHeaderVars(array("", "title"), $header_params);
		$tbl->setColumnWidth(array("0%", "100%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		
		// get style ids
		$style_ids = $this->object->getStyles();
		
		// todo
		$tbl->setMaxCount(count($style_ids));

		$this->tpl->setVariable("COLUMN_COUNTS", 2);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		$this->showActions(true);

		include_once ("classes/class.ilObjStyleSheet.php");

		foreach ($style_ids as $style_id)
		{
			$this->tpl->setCurrentBlock("style_row");
		
			// color changing
			$css_row = ($css_row == "tblrow2")
				? "tblrow1"
				: "tblrow2";

			$this->tpl->setVariable("CHECKBOX_ID", $style_id);
			$this->tpl->setVariable("TXT_TITLE", ilObject::_lookupTitle($style_id));
			$this->tpl->setVariable("TXT_DESC", ilObject::_lookupDescription($style_id));
			$this->tpl->setVariable("LINK_STYLE",
				"adm_object.php?ref_id=".$_GET["ref_id"].
				"&obj_id=".$style_id);
			$this->tpl->setVariable("ROWCOL", $css_row);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("tbl_content");
			$this->tpl->parseCurrentBlock();

		} //if is_array
		
		if (count($style_ids) == 0)
		{
            $tbl->disable("header");
			$tbl->disable("footer");
			
			$this->tpl->setCurrentBlock("text");
			$this->tpl->setVariable("TXT_CONTENT", $this->lng->txt("obj_not_found"));
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("tbl_content");
			$this->tpl->parseCurrentBlock();
		}
		
		

		// render table
		$tbl->render();
	}
	
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		// tabs are defined manually here. The autogeneration via objects.xml will be deprecated in future
		// for usage examples see ilObjGroupGUI or ilObjSystemFolderGUI
	}
} // END class.ilObjStyleSheetFolder
?>
