<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use \Psr\Http\Message\RequestInterface;

/**
 * Classification provider
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
abstract class ilClassificationProvider
{
    protected int $parent_ref_id;
    protected int $parent_obj_id;
    protected string $parent_type;
    protected RequestInterface $request;
    
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
        global $DIC;

        $this->request = $DIC->http()->request();
        $this->parent_ref_id = (int) $a_parent_ref_id;
        $this->parent_obj_id = (int) $a_parent_obj_id;
        $this->parent_type = (string) $a_parent_obj_type;
        
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
     * @return array
     */
    public static function getValidProviders($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type)
    {
        $res = array();
        
        if (ilTaxonomyClassificationProvider::isActive($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type)) {
            $res[] = new ilTaxonomyClassificationProvider($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type);
        }
        
        if (ilTaggingClassificationProvider::isActive($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type)) {
            $res[] = new ilTaggingClassificationProvider($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type);
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
     * @param object $a_parent_gui
     */
    abstract public function render(array &$a_html, $a_parent_gui);
    
    /**
     * Import post data
     *
     * @param mixed $a_saved
     * @return mixed
     */
    abstract public function importPostData($a_saved = null);
    
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
    abstract public function getFilteredObjects() : array;
    
    
    /**
     * Init list gui properties
     *
     * @param ilObjectListGUI $a_list_gui
     */
    public function initListGUI(ilObjectListGUI $a_list_gui)
    {
    }
}
