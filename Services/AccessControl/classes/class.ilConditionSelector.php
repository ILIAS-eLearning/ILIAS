<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php";

/**
* Repository Explorer
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/
class ilConditionSelector extends ilRepositorySelectorExplorerGUI
{

	/**
	 * Construct
	 *
	 * @param object $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param object $a_selection_gui
	 * @param string $a_selection_cmd
	 * @param string $a_selection_par
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_selection_gui = null, $a_selection_cmd = "add",
								$a_selection_par = "source_id")
	{

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_selection_gui, $a_selection_cmd,
			$a_selection_par);

		//TODO: find a stable way for ajax calls!
		$this->setAjax(false);
	}

	/**
	 * Is node visible
	 *
	 * @param array $a_node node data
	 * @return bool visible true/false
	 */
	function isNodeVisible($a_node)
	{
		global $ilAccess;

		if (!$ilAccess->checkAccess('read', '', $a_node["child"]))
		{
			return false;
		}
		return true;
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

		//has node a clickable type?
		if(count($this->getClickableTypes())>0)
		{
			if(!in_array($a_node["type"], $this->getClickableTypes()))
			{
				return false;
			}
		}

		if($a_node["child"] == $this->getRefId())
		{
			return false;
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

	/**
	 * set ref id of target object
	 *
	 * @param $a_ref_id
	 */
	function setRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}

	/**
	 * get ref id of target object
	 *
	 * @return mixed
	 */
	function getRefId()
	{
		return $this->ref_id;
	}


} 