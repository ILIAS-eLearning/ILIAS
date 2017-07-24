<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");

/**
 * Action target explorer
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id$
 *
 * @ingroup ModulesGroup
 */
class ilGroupActionTargetExplorerGUI extends ilRepositorySelectorExplorerGUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd, null, "", "");
	}

	/**
	 * Set clickable type
	 *
	 * @param string $a_val clickable type	
	 */
	function setClickableType($a_val)
	{
		$this->clickable_type = $a_val;
	}
	
	/**
	 * Get clickable type
	 *
	 * @return string clickable type
	 */
	function getClickableType()
	{
		return $this->clickable_type;
	}

	/**
	 * Get onclick attribute
	 */
	function getNodeOnClick($a_node)
	{
		$this->ctrl->setParameter($this->parent_obj, "grp_act_ref_id", $a_node["child"]);
		$url = $this->ctrl->getLinkTarget($this->parent_obj, "confirmAddUser", "", true, false);
		return "il.Util.ajaxReplaceInner('$url', 'il_grp_action_modal_content'); return(false);";
		//return "il.Group.UserActions.selectTargetObject('".$a_node["type"]."','".$a_node["child"]."'); return(false);";
	}

	/**
	 * Is node clickable?
	 *
	 * @param array $a_node node data
	 * @return boolean node clickable true/false
	 */
	function isNodeClickable($a_node)
	{
		if ($a_node["type"] == $this->getClickableType())
		{
			return true;
		}
		return false;
	}

}

?>
