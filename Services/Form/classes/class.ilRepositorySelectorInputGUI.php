<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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

include_once("./Services/Table/interfaces/interface.ilTableFilterItem.php");
include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");

/**
* This class represents a repository selector in a property form.
*
* The implementation is kind of beta. It looses all other inputs, if the
* selector link is used.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
* @ilCtrl_IsCalledBy ilRepositorySelectorInputGUI: ilFormPropertyDispatchGUI
*/
class ilRepositorySelectorInputGUI extends ilFormPropertyGUI implements ilTableFilterItem
{
	protected $options;
	protected $value;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		global $lng;
		
		parent::__construct($a_title, $a_postvar);
		$this->setType("rep_select");
		$this->setSelectText($lng->txt("select"));
	}

	/**
	* Set Value.
	*
	* @param	int 		ref id of selected repository item
	*/
	function setValue($a_value)
	{
		$this->value = $a_value;
	}

	/**
	* Get Value.
	*
	* @return	int 		ref id of selected repository item
	*/
	function getValue()
	{
		return $this->value;
	}
	
	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		$this->setValue($a_values[$this->getPostVar()]);
	}

	/**
	* Set select link text
	*
	* @param	string	select link text
	*/
	function setSelectText($a_val)
	{
		$this->select_text = $a_val;
	}
	
	/**
	* Get select link text
	*
	* @return	string	select link text
	*/
	function getSelectText()
	{
		return $this->select_text;
	}
	
	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$_POST[$this->getPostVar()] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]);

		if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		return true;
	}

	/**
	* Select Repository Item
	*/
	function showRepositorySelection()
	{
		global $tpl, $lng, $ilCtrl, $tree, $ilUser;
		
		include_once 'Services/Search/classes/class.ilSearchRootSelector.php';
		$ilCtrl->setParameter($this, "postvar", $this->getPostVar());

		ilUtil::sendInfo($lng->txt('search_area_info'));
		
		$exp = new ilSearchRootSelector($ilCtrl->getLinkTarget($this,'showRepositorySelection'));
		$exp->setExpand($_GET["search_root_expand"] ? $_GET["search_root_expand"] : $tree->readRootId());
		$exp->setExpandTarget($ilCtrl->getLinkTarget($this,'showRepositorySelection'));
		$exp->setTargetClass(get_class($this));
		$exp->setCmd('selectRepositoryItem');

		// build html-output
		$exp->setOutput(0);
		$tpl->setContent($exp->getOutput());
	}
	
	/**
	* Select repository item
	*/
	function selectRepositoryItem()
	{
		global $ilCtrl, $ilUser;

		$anchor = $ilUser->prefs["screen_reader_optimization"]
			? $this->getFieldId()."_anchor"
			: "";

		$this->setValue($_GET["root_id"]);
		$this->writeToSession();

		$ilCtrl->returnToParent($this, $anchor);
	}
	
	
	/**
	* Render item
	*/
	function render($a_mode = "property_form")
	{
		global $lng, $ilCtrl, $ilObjDataCache, $tree;
		
		$tpl = new ilTemplate("tpl.prop_rep_select.html", true, true, "Services/Form");

		$tpl->setVariable("POST_VAR", $this->getPostVar());
		$tpl->setVariable("ID", $this->getFieldId());
		$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
		$tpl->setVariable("TXT_SELECT", $this->getSelectText());
		switch ($a_mode)
		{
			case "property_form":
				$parent_gui = "ilpropertyformgui";
				break;
				
			case "table_filter":
				$parent_gui = get_class($this->getParent());
				break;
		}

		$ilCtrl->setParameterByClass("ilrepositoryselectorinputgui",
			"postvar", $this->getPostVar());
		$tpl->setVariable("HREF_SELECT",
			$ilCtrl->getLinkTargetByClass(array($parent_gui, "ilformpropertydispatchgui", "ilrepositoryselectorinputgui"),
			"showRepositorySelection"));

		if ($this->getValue() > 0 && $this->getValue() != ROOT_FOLDER_ID)
		{
			$tpl->setVariable("TXT_ITEM",
				$ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($this->getValue())));
		}
		else
		{
			$nd = $tree->getNodeData(ROOT_FOLDER_ID);
			$title = $nd["title"];
			if ($title == "ILIAS")
			{
				$title = $lng->txt("repository");
			}
			$tpl->setVariable("TXT_ITEM", $title);
		}
		return $tpl->get();
	}
	
	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $this->render());
		$a_tpl->parseCurrentBlock();
	}

	/**
	* Get HTML for table filter
	*/
	function getTableFilterHTML()
	{
		$html = $this->render("table_filter");
		return $html;
	}

}
