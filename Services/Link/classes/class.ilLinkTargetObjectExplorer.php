<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");

/**
 * Internal Link: Repository Item Selector Explorer
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesIntLink
 */
class ilLinkTargetObjectExplorer extends ilExplorer
{
	/**
	 * Constructor
	 */
	function __construct($a_target)
	{
		parent::__construct($a_target);
	}


	/**
	 * Get onclick attribute
	 */
	function buildOnClick($a_node_id, $a_type, $a_title)
	{
		return "il.IntLink.selectLinkTargetObject('".$a_type."','".$a_node_id."'); return(false);";
	}
}

?>
