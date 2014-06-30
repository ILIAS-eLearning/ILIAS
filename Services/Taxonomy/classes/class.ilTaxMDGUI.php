<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Taxonomies selection for metadata helper GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @package ilias-core
 * @version $Id: class.ilMDEditorGUI.php 36575 2012-08-28 12:17:50Z jluetzen $
 * @ingroup ServicesTaxonomy
 */
class ilTaxMDGUI
{
	protected $md_rbac_id; // [int]
	protected $md_obj_id; // [int]
	protected $md_obj_type; // [string]
	
	/**
	 * Constructor
	 * 
	 * @param int $a_md_rbac_id
	 * @param int $a_md_obj_id
	 * @param int $a_md_obj_type
	 * @return self
	 */
	public function __construct($a_md_rbac_id, $a_md_obj_id, $a_md_obj_type)
	{
		$this->md_rbac_id = $a_md_rbac_id;
		$this->md_obj_id = $a_md_obj_id;
		$this->md_obj_type = $a_md_obj_type;				
	}
	
	/**
	 * Get selectable taxonomies for current object
	 * 
	 * @return array
	 */
	protected function getSelectableTaxonomies()
	{
		global $objDefinition, $tree;
		
		if($objDefinition->isRBACObject($this->md_obj_type))
		{
			$res = array();
			
			// see ilTaxonomyBlockGUI::getActiveTaxonomies()
						
			// get all active taxonomies of parent objects
			foreach($tree->getPathFull((int)$_REQUEST["ref_id"]) as $node)
			{				
				// currently only active for categories
				if($node["type"] == "cat")
				{
					include_once "Services/Object/classes/class.ilObjectServiceSettingsGUI.php";
					include_once "Services/Container/classes/class.ilContainer.php";
					if(ilContainer::_lookupContainerSetting(
						$node["obj_id"],
						ilObjectServiceSettingsGUI::TAXONOMIES,
						false
						))
					{
						include_once "Services/Taxonomy/classes/class.ilObjTaxonomy.php";
						$tax_ids = ilObjTaxonomy::getUsageOfObject($node["obj_id"]);					
						if(sizeof($tax_ids))
						{
							$res = array_merge($res, $tax_ids);
						}
					}
				}
			}		
			
			if(sizeof($res))
			{
				return $res;
			}
		}
	}
	
	/**
	 * Init tax node assignment
	 * 
	 * @param int $a_tax_id
	 * @return ilTaxNodeAssignment
	 */
	protected function initTaxNodeAssignment($a_tax_id)
	{
		include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
		return new ilTaxNodeAssignment($this->md_obj_type, $this->md_obj_id, "obj", $a_tax_id);			
	}
	
	/**
	 * Add taxonomy selector to MD (quick edit) form
	 * 
	 * @param ilPropertyFormGUI $a_form
	 */
	public function addToMDForm(ilPropertyFormGUI $a_form)
	{		
		$tax_ids = $this->getSelectableTaxonomies();
		if(is_array($tax_ids))
		{
			include_once "Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php";								
			foreach($tax_ids as $tax_id)
			{														
				// get existing assignments
				$node_ids = array();				
				$ta = $this->initTaxNodeAssignment($tax_id);							
				foreach($ta->getAssignmentsOfItem($this->md_obj_id) as $ass)
				{
					$node_ids[] = $ass["node_id"];
				}
				
				$tax_sel = new ilTaxSelectInputGUI($tax_id, "md_tax_".$tax_id, true);
				$tax_sel->setValue($node_ids);
				$a_form->addItem($tax_sel);				
			}
		}		
	}
	
	/**
	 * Import settings from MD (quick edit) form
	 */
	public function updateFromMDForm()
	{
		$tax_ids = $this->getSelectableTaxonomies();
		if(is_array($tax_ids))
		{
			include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
			
			foreach($tax_ids as $tax_id)
			{				
				$ta = $this->initTaxNodeAssignment($tax_id);	
				
				// delete existing assignments
				$ta->deleteAssignmentsOfItem($this->md_obj_id);
							
				// set current assignment
				if(is_array($_POST["md_tax_".$tax_id]))
				{
					foreach($_POST["md_tax_".$tax_id] as $node_id)
					{
						$ta->addAssignment($node_id, $this->md_obj_id);
					}
				}
			}
		}				
	}	
}
