<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Select media pool for adding objects into pages
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/

include_once "./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php";

class ilPoolSelectorGUI extends ilRepositorySelectorExplorerGUI
{
	protected $clickable_types = array();
	protected $selection_subcmd = "";


	/**
	 * Constructor
	 *
	 * @param object $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param object $a_selection_gui
	 * @param string $a_selection_cmd
	 * @param string $a_selection_par
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_selection_gui = null, $a_selection_cmd = "insert",
						 $a_selection_subcmd = "selectPool", $a_selection_par = "pool_ref_id")
	{
		if($a_selection_gui == null)
		{
			$a_selection_gui = $a_parent_obj;
		}

		$this->selection_subcmd = $a_selection_subcmd;
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_selection_gui, $a_selection_cmd,
			$a_selection_par);

		$this->setAjax(false);
	}

	/**
	 * Get href for node
	 *
	 * @param mixed $a_node node object/array
	 * @return string href attribute
	 */
	function getNodeHref($a_node)
	{
		global $ilCtrl;
		
		$ilCtrl->setParameterByClass($this->selection_gui, "subCmd", $this->selection_subcmd);
		$link = parent::getNodeHref($a_node);
		$ilCtrl->setParameterByClass($this->selection_gui, "subCmd", "");
		return $link;
	}

	/**
	 * Is node visible
	 *
	 * @param array $a_node node data
	 * @return bool visible true/false
	 */
	function isNodeVisible($a_node)
	{
		if(!parent::isNodeVisible($a_node))
			return false;

		//hide empty container
		if(count($this->getChildsOfNode($a_node["child"]))>0 || $this->isNodeClickable($a_node))
			return true;
		else
			return false;
	}

	/**
	 * Is node clickable?
	 *
	 * @param array $a_node node data
	 * @return boolean node clickable true/false
	 */
	function isNodeClickable($a_node)
	{
		if(!parent::isNodeClickable($a_node))
			return false;

		if(count($this->getClickableTypes())>0)
		{
			return in_array($a_node["type"], $this->getClickableTypes());
		}

		return true;
	}

	/**
	 * set Whitelist for clickable items
	 *
	 * @param array/string $a_types array type
	 */
	function setClickableTypes($a_types)
	{
		if(!is_array($a_types))
		{
			$a_types = array($a_types);
		}
		$this->clickable_types = $a_types;
	}

	/**
	 * get whitelist for clickable items
	 *
	 * @return array types
	 */
	function getClickableTypes()
	{
		return (array)$this->clickable_types;
	}
}