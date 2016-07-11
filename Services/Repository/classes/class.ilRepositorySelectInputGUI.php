<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/UIComponent/Explorer2/classes/class.ilExplorerSelectInputGUI.php");
/**
 * Select taxonomy nodes input GUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_IsCalledBy ilRepositorySelectInputGUI: ilFormPropertyDispatchGUI
 *
 * @ingroup	ServicesRepository
 */
class ilRepositorySelectInputGUI extends ilExplorerSelectInputGUI
{
	/**
	 * @var ilRepositorySelectorExplorerGUI
	 */
	protected $explorer_gui;
	/**
	 * @var string[]
	 */
	protected $container_types = array("root", "cat", "grp", "fold", "crs");
	/**
	 * @var string[]
	 */
	protected $clickable_types = array();

	/**
	 * @var bool
	 */
	protected $multi_nodes = false;
	/**
	 * Constructor
	 *
	 * @param	string	$a_title	Title
	 * @param	string	$a_postvar	Post Variable
	 */
	function __construct($a_title, $a_postvar, $a_multi = false)
	{
		global $ilCtrl, $lng;
		$this->multi_nodes = $a_multi;
		include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");
		$ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $a_postvar);
		$explorer_gui = new ilRepositorySelectorExplorerGUI(array("ilpropertyformgui", "ilformpropertydispatchgui", "ilRepositorySelectInputGUI"), $this->getExplHandleCmd(), "");
		$explorer_gui->setSelectMode($a_postvar . "_sel", $a_multi);
		$explorer_gui->setSkipRootNode(true);
		$this->setType("rep_select");
		//$this->setSelectText($lng->txt("select"));
		parent::__construct($a_title, $a_postvar, $explorer_gui);
	}
	function getTitleForNodeId($a_id)
	{
		return ilObject::_lookupTitle(ilObject::_lookupObjectId($a_id));
	}
	/**
	 * Set clickable types
	 *
	 * @param string[] $a_types
	 */
	function setClickableTypes($a_types)
	{
		$this->clickable_types = $a_types;
	}
	/**
	 * Get  clickable types
	 *
	 * @return string[]
	 */
	function getClickableTypes()
	{
		return $this->clickable_types;
	}
	/**
	 * returns all visible types like container and clickable types
	 *
	 * @return array
	 */
	protected function getVisibleTypes()
	{
		return array_merge((array)$this->container_types, (array)$this->getClickableTypes());
	}
	/**
	 * Returns the highlighted object
	 *
	 * @return int ref_id (node)
	 */
	protected function getHighlightedNode()
	{
		global $tree;
		if(!$this->getValue() || $this->getMulti())
		{
			return "";
		}
		if(!in_array(ilObject::_lookupType($this->getValue(),true), $this->getVisibleTypes()))
		{
			return $tree->getParentId($this->getValue());
		}
		return $this->getValue();
	}
	/**
	 * Render item
	 */
	function render($a_mode = "property_form")
	{
		$this->explorer_gui->setClickableTypes($this->getClickableTypes());
		//$this->explorer_gui->setSelectableTypes($this->getClickableTypes());
		$this->explorer_gui->setTypeWhiteList($this->getVisibleTypes());
		if($this->getValue() &&  !$this->getMulti())
		{
			$this->explorer_gui->setPathOpen($this->getValue());
			$this->explorer_gui->setHighlightedNode($this->getHighlightedNode());
		}

		if($this->multi_nodes)
		{
			$this->explorer_gui->setSelectMode($this->getPostVar(), true);
		}

		return parent::render($a_mode);
	}
	/**
	 * overwrites handleExplorerCommand
	 */
	function handleExplorerCommand()
	{
		$this->explorer_gui->setClickableTypes($this->getClickableTypes());
		//$this->explorer_gui->setSelectableTypes($this->getClickableTypes());
		$this->explorer_gui->setTypeWhiteList($this->getVisibleTypes());

		if($this->multi_nodes)
		{
			$this->explorer_gui->setSelectMode($this->getPostVar(), true);
		}

		parent::handleExplorerCommand();
	}

	function setMulti($a_multi, $a_sortable = false, $a_addremove = true)
	{
		$this->multi_nodes = true;
	}

	function getMulti()
	{
		return $this->multi_nodes;
	}
}