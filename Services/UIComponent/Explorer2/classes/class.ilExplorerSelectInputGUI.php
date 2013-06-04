<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/interfaces/interface.ilTableFilterItem.php");
include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");

/**
 * Select explorer tree nodes input GUI
 *
 * @author Alex Killing <alex.killing@gmx.de> 
 * @version $Id$
 *
 * @ingroup	ServicesForm
 */
class ilExplorerSelectInputGUI extends ilFormPropertyGUI implements ilTableFilterItem
{
	/**
	 * Constructor
	 *
	 * @param	string	$a_title	Title
	 * @param	string	$a_postvar	Post Variable
	 */
	function __construct($a_title, $a_postvar, $a_explorer_gui, $a_multi = false)
	{
		global $lng;
		
		$this->multi = $a_multi;
		$this->explorer_gui = $a_explorer_gui;
		
		parent::__construct($a_title, $a_postvar);
		$this->setType("exp_select");		
	}

	/**
	 * Get explorer handle command function
	 *
	 * @param
	 * @return
	 */
	function getExplHandleCmd()
	{
		return "handleExplorerCommand";
	}
	
	/**
	 * Handle explorer command
	 */
	function handleExplorerCommand()
	{
		$this->explorer_gui->handleCommand();
	}
	
	
	/**
	 * Set Value.
	 *
	 * @param mixed tax node id or array of node ids (multi mode)
	 */
	function setValue($a_value)
	{
		if ($this->multi && !is_array($a_value))
		{
			$this->value = array($a_value);
		}
		else
		{
			$this->value = $a_value;
		}
	}

	/**
	 * Get Value.
	 *
	 * @return mixed tax node id or array of node ids (multi mode)
	 */
	function getValue()
	{
		return $this->value;
	}

	/**
	 * Check input, strip slashes etc. set alert, if input is not ok.
	 *
	 * @return	boolean		Input ok, true/false
	 */	
	function checkInput()
	{
		global $lng;
		
		// sanitize
		if ($this->multi)
		{
			if (!is_array($_POST[$this->getPostVar()]))
			{
				$_POST[$this->getPostVar()] = array();
			}
			
			foreach ($_POST[$this->getPostVar()] as $k => $v)
			{
				$_POST[$this->getPostVar()][$k] = (int) $v;
			}
		}
		else
		{
			$_POST[$this->getPostVar()] = (int) $_POST[$this->getPostVar()];
		}
		
		// check required
		if ($this->getRequired())
		{
			if ((!$this->multi && trim($_POST[$this->getPostVar()]) == "") ||
				($this->multi && count($_POST[$this->getPostVar()]) == 0))
			{
				$this->setAlert($lng->txt("msg_input_is_required"));
				return false;
			}
		}
		return true;
	}

	
	/**
	 * Render item
	 */
	function render($a_mode = "property_form")
	{
		global $lng, $ilCtrl, $ilObjDataCache, $tree;
		
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initPanel();
		$GLOBALS["tpl"]->addJavascript("./Services/UIComponent/Explorer2/js/Explorer2.js");
		
		$tpl = new ilTemplate("tpl.prop_expl_select.html", true, true, "Services/UIComponent/Explorer2");

		$tpl->setVariable("POST_VAR", $this->getPostVar());
		$tpl->setVariable("ID", $this->getFieldId());
//		$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
		$tpl->setVariable("TXT_SELECT", $lng->txt("select"));
		$tpl->setVariable("TXT_RESET", $lng->txt("reset"));
		
		$tpl->setVariable("EXPL", $this->explorer_gui->getHTML());

		//$tpl->setVariable("HREF_SELECT",
		//	$ilCtrl->getLinkTargetByClass(array($parent_gui, "ilformpropertydispatchgui", "ilrepositoryselectorinputgui"),
		//	"showRepositorySelection"));

		/*if ($this->getValue() > 0 && $this->getValue() != ROOT_FOLDER_ID)
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
			if (in_array($nd["type"], $this->getClickableTypes()))
			{
				$tpl->setVariable("TXT_ITEM", $title);
			}
		}*/
		
		return $tpl->get();
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
