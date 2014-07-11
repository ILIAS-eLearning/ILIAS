<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/OrgUnit/classes/class.ilOrgUnitExplorerGUI.php");

/**
 * Class ilTEPOrgUnitExplorerGUI
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesTEP
 */
class ilTEPOrgUnitExplorerGUI extends ilOrgUnitExplorerGUI
{		
	protected $sel_orgu_ids; // [array]
	
	public function __construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $a_tree)
	{
		parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $a_tree);
		$this->setTypeWhiteList(array("orgu"));
	}
	
	
	//
	// properties
	// 
	
	public function setSelectableOrgUnitIds(array $a_ids)
	{
		$this->sel_orgu_ids = $a_ids;
	}
	
	public function getSelectableOrgUnitIds()
	{
		return $this->sel_orgu_ids;
	}
	
	
	//
	// selection only, not clickable
	// 
	
	public function getNodeHref($node)
	{		
		return;		
	}
		
	public function isNodeClickable($a_node)
	{
		return;
	}

	protected function getLinkTarget()
	{
		return;
	}

	//
	// access control
	//
	 
	function getChildsOfNode($a_parent_node_id)
	{
		global $ilAccess;

		$wl = $this->getTypeWhiteList();
		if (is_array($wl) && count($wl) > 0)
		{
			$childs = $this->tree->getChildsByTypeFilter($a_parent_node_id, $wl, $this->getOrderField());
		}
		else
		{
			$childs = $this->tree->getChilds($a_parent_node_id, $this->getOrderField());
		}

		// apply black list filter
		$bl = $this->getTypeBlackList();
		if (is_array($bl) && count($bl) > 0)
		{
			$bl_childs = array();
			foreach($childs as $k => $c)
			{
				if (!in_array($c["type"], $bl))
				{
					$bl_childs[$k] = $c;
				}
			}
			return $bl_childs;
		}
		
		
		// :TODO: display ALL nodes?
		
		return $childs;
	}
	
	protected function isNodeSelectable(array $a_node)
	{
		$selectable = $this->getSelectableOrgUnitIds();
		if(is_array($selectable) && !in_array($a_node["ref_id"], $selectable))
		{
			return false;
		}
		return true;
	}
}