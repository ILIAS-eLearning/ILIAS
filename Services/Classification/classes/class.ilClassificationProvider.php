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
	protected $parent_type; // [string]
	protected $active_filter; // [bool]
	
	/**
	 * Constructor 
	 * 
	 * @param int $a_parent_ref_id
	 * @param int $a_parent_obj_id
	 * @param string $a_parent_obj_type
	 * @return self
	 */
	public function __construct($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type, $a_active_filter)
	{
		$this->parent_ref_id = (int)$a_parent_ref_id;
		$this->parent_obj_id = (int)$a_parent_obj_id;
		$this->parent_type = (string)$a_parent_obj_type;
		$this->active_filter = (bool)$a_active_filter;
		
		$this->init();
	}
	
	/**
	 * Instance initialisation
	 */
	protected function init()
	{
		
	}
		
	/**
	 * Get all valid providers (for parent container) 
	 * 
	 * @param int $a_parent_ref_id
	 * @param int $a_parent_obj_id
	 * @param string $a_parent_obj_type
	 * @param bool $a_active_filter
	 * @return array
	 */
	public static function getValidProviders($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type, $a_active_filter)
	{
		$res = array();
		
		include_once "Services/Taxonomy/classes/class.ilTaxonomyClassificationProvider.php";
		if(ilTaxonomyClassificationProvider::isActive($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type))
		{
			$res[] = new ilTaxonomyClassificationProvider($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type, $a_active_filter);
		}
		
		include_once "Services/Tagging/classes/class.ilTaggingClassificationProvider.php";
		if(ilTaggingClassificationProvider::isActive($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type))
		{
			$res[] = new ilTaggingClassificationProvider($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type, $a_active_filter);
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
	
	/**
	 * Import post data
	 * 
	 * @return mixed
	 */
	abstract public function importPostData();
	
	/**
	 * Set selection
	 * 
	 * @param mixed $a_value
	 */
	abstract public function setSelection($a_value);
	
	/**
	 * Get filtered object ref ids
	 * 
	 * @return array
	 */
	abstract public function getFilteredObjects();			
}