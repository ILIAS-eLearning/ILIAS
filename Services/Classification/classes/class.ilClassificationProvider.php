<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Classification provider
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesClassification
 */
abstract class ilClassificationProvider
{	
	protected $parent_ref_id; // [int]
	protected $parent_obj_id; // [int]
	
	/**
	 * Constructor 
	 * 
	 * @param int $a_parent_ref_id
	 * @param int $a_parent_obj_id
	 * @param string $a_parent_obj_type
	 * @return self
	 */
	public function __construct($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type)
	{
		$this->parent_ref_id = (int)$a_parent_ref_id;
		$this->parent_obj_id = (int)$a_parent_obj_id;
		$this->parent_type = (string)$a_parent_obj_type;
	}
		
	/**
	 * Get all valid providers (for parent container) 
	 * 
	 * @param int $a_parent_ref_id
	 * @param int $a_parent_obj_id
	 * @param string $a_parent_obj_type
	 * @return array
	 */
	public static function getValidProviders($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type)
	{
		$res = array();
		
		include_once "Services/Taxonomy/classes/class.ilTaxonomyClassificationProvider.php";
		if(ilTaxonomyClassificationProvider::isActive($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type))
		{
			$res[] = new ilTaxonomyClassificationProvider($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type);
		}
		
		return $res;
	}
	
	/**
	 * Is provider currently active?
	 * 
	 * @param int $a_parent_ref_id
	 * @param int $a_parent_obj_id
	 * @param string $a_parent_obj_type
	 * @return bool
	 */
	abstract public static function isActive($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type);	
	
	/**
	 * Render HTML chunks
	 * 
	 * @param array $a_html
	 */
	abstract public function render(array &$a_html, $a_parent_gui, $a_parent_cmd, $a_target_gui, $a_target_cmd);
}