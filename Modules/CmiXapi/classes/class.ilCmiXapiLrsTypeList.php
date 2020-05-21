<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiLrsTypeList
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiLrsTypeList
{
    /**
     * Get basic data array of all types (without field definitions)
     *
     * @param	boolean		get extended data ('usages')
     * @param	mixed		required availability or null
     * @return	array		array of assoc data arrays
     */
    public static function getTypesData($a_extended = false, $a_availability = null)
    {
        global $ilDB;
        
        $query = "SELECT * FROM " . ilCmiXapiLrsType::getDbTableName();
        if (isset($a_availability)) {
            $query .= " WHERE availability=" . $ilDB->quote($a_availability, 'integer');
        }
        $query .= " ORDER BY title";
        $res = $ilDB->query($query);
        
        $data = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $row['lrs_type_id'] = $row['type_id']; // indeed it is an lrs-type-id
            
            if ($a_extended) {
                $row['usages'] = self::countUntrashedUsages($row['lrs_type_id']);
            }
            $data[] = $row;
        }
        return $data;
    }
    
    /**
     * Count the number of untrashed usages of a type
     *
     * @var		integer		type_id
     * @return	integer		number of references
     */
    public static function countUntrashedUsages($a_type_id)
    {
        global $ilDB;
        
        $query = "
			SELECT COUNT(*) untrashed
			FROM cmix_settings s
			INNER JOIN object_reference r
			ON r.obj_id = s.obj_id
			AND r.deleted IS NULL
			WHERE s.lrs_type_id = %s
		";
        
        $res = $ilDB->queryF($query, ['integer'], [$a_type_id]);
        $row = $ilDB->fetchObject($res);
        return $row->untrashed;
    }
    
    /**
     * Get array of options for selecting the type
     *
     * @param	mixed		required availability or null
     * @return	array		id => title
     */
    public static function getTypeOptions($a_availability = null)
    {
        global $ilDB;
        
        $query = "SELECT * FROM " . ilCmiXapiLrsType::getDbTableName();
        if (isset($a_availability)) {
            $query .= " WHERE availability=" . $ilDB->quote($a_availability, 'integer');
        }
        $res = $ilDB->query($query);
        
        $options = array();
        while ($row = $ilDB->fetchObject($res)) {
            $options[$row->type_id] = $row->title;
        }
        return $options;
    }
    
    public static function getTypesStruct()
    {
        $a_s = array(
            'type_name' => array('type' => 'text', 'maxlength' => 32)
        , 'title' => array('type' => 'text', 'maxlength' => 255)
        , 'description' => array('type' => 'text', 'maxlength' => 4000)
        , 'availability' => array('type' => 'a_integer', 'maxlength' => 1,'options' => array(2,1,0)) //AVAILABILITY_CREATE,AVAILABILITY_EXISTING,AVAILABILITY_NONE
            // , 'lrs'				=> array('type'=>'headline')
        , 'lrs_type_id' => array('type' => 'a_integer', 'maxlength' => 1, 'options' => array(0))
        , 'lrs_endpoint' => array('type' => 'text', 'maxlength' => 64, 'required' => true)
        , 'lrs_key' => array('type' => 'text', 'maxlength' => 64, 'required' => true)
        , 'lrs_secret' => array('type' => 'text', 'maxlength' => 64, 'required' => true)
        , 'external_lrs' => array('type' => 'bool')
        , 'privacy_ident' => array('type' => 'a_integer', 'maxlength' => 1, 'options' => array(0,1,2,3))
        , 'privacy_name' => array('type' => 'a_integer', 'maxlength' => 1, 'options' => array(0,1,2,3))
        , 'privacy_comment_default' => array('type' => 'text', 'maxlength' => 2000)
        , 'remarks' => array('type' => 'text', 'maxlength' => 4000)
        );
        return $a_s;
    }
    
    public static function getCountTypesForCreate()
    {
        global $ilDB;
        $query = "SELECT COUNT(*) counter FROM " . ilCmiXapiLrsType::getDbTableName() . "
					WHERE availability = " . $ilDB->quote(ilCmiXapiLrsType::AVAILABILITY_CREATE, 'integer');
        $res = $ilDB->query($query);
        $row = $ilDB->fetchObject($res);
        return $row->counter;
    }
}
