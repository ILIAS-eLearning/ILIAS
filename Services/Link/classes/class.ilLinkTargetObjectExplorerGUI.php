<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");

/**
 * Internal Link: Repository Item Selector Explorer
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesIntLink
 */
class ilLinkTargetObjectExplorerGUI extends ilRepositorySelectorExplorerGUI
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
		return "il.IntLink.selectLinkTargetObject('".$a_node["type"]."','".$a_node["child"]."'); return(false);";
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
