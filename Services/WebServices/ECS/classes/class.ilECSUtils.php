<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSUtils
{
    public const TYPE_ARRAY = 1;
    public const TYPE_INT = 2;
    public const TYPE_STRING = 3;
    public const TYPE_TIMEPLACE = 4;
        
    /**
     * get optional econtent fields
     * These fields might be mapped against AdvancedMetaData field definitions
     */
    public static function _getOptionalEContentFields() : array
    {
        // :TODO: ?
        $def = self::getEContentDefinition('/campusconnect/courselinks');
        return array_keys($def);
    }
    
    /**
     * get optional econtent fields
     * These fields might be mapped against AdvancedMetaData field definitions
     */
    public static function _getOptionalECourseFields() : array
    {
        // :TODO: ?
        $def = self::getEContentDefinition('/campusconnect/courselinks');
        return array_keys($def);
    }

    /**
     * Get all possible remote object types
     */
    public static function getPossibleRemoteTypes(bool $a_with_captions = false) : array
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $all = array("rcrs", "rcat", "rfil", "rglo", "rgrp", "rlm", "rwik");
        
        if (!$a_with_captions) {
            return $all;
        }
        
        $res = array();
        foreach ($all as $id) {
            $res[$id] = $lng->txt("obj_" . $id);
        }
        return $res;
    }
    
    /**
     * Get all possible release object types
     */
    public static function getPossibleReleaseTypes(bool $a_with_captions = false) : array
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $all = array("crs", "cat", "file", "glo", "grp", "lm", "wiki");
        
        if (!$a_with_captions) {
            return $all;
        }
        
        $res = array();
        foreach ($all as $id) {
            $res[$id] = $lng->txt("obj_" . $id);
        }
        return $res;
    }
    
    /**
     * Get econtent / metadata definition
     */
    public static function getEContentDefinition(string $a_resource_id) : array
    {
        switch ($a_resource_id) {
            case '/campusconnect/courselinks':
                return array(
                    'study_courses' => self::TYPE_ARRAY,
                    'lecturer' => self::TYPE_ARRAY,
                    'courseType' => self::TYPE_STRING,
                    'courseID' => self::TYPE_INT,
                    'credits' => self::TYPE_INT,
                    'semester_hours' => self::TYPE_INT,
                    'term' => self::TYPE_STRING,
                    'begin' => array(self::TYPE_TIMEPLACE, 'timePlace'),
                    'end' => array(self::TYPE_TIMEPLACE, 'timePlace'),
                    'room' => array(self::TYPE_TIMEPLACE, 'timePlace'),
                    'cycle' => array(self::TYPE_TIMEPLACE, 'timePlace')
                );
                
            case '/campusconnect/categories':
            case '/campusconnect/files':
            case '/campusconnect/glossaries':
            case '/campusconnect/groups':
            case '/campusconnect/learningmodules':
            case '/campusconnect/wikis':
                // no metadata mapping yet
            default:
                return [];
        }
    }
    
    /**
     * Convert ECS content to rule matchable values
     */
    public static function getMatchableContent(string $a_resource_id, int $a_server_id, object $a_ecs_content, int $a_owner) : array
    {
        // see ilECSCategoryMapping::getPossibleFields();
        $res = array();
        $res["part_id"] = array($a_owner, ilECSCategoryMappingRule::ATTR_INT);
        $res["community"] = array(ilECSCommunitiesCache::getInstance()->lookupTitle($a_server_id, $a_owner),
            ilECSCategoryMappingRule::ATTR_STRING);
        
        $definition = self::getEContentDefinition($a_resource_id);
        
        $timePlace = null;
        $value = null;
        foreach ($definition as $id => $type) {
            if (is_array($type)) {
                [$type, $target] = $type;
            } else {
                $target = $id;
            }
            switch ($type) {
                case self::TYPE_ARRAY:
                    if (isset($a_ecs_content->{$target})) {
                        $value = array(implode(',', (array) $a_ecs_content->{$target}), ilECSCategoryMappingRule::ATTR_ARRAY);
                    } else {
                        $value = [];
                    }
                    break;

                case self::TYPE_INT:
                    if (isset($a_ecs_content->{$target})) {
                        $value = array((int) $a_ecs_content->{$target}, ilECSCategoryMappingRule::ATTR_INT);
                    } else {
                        $value = 0;
                    }
                    break;

                case self::TYPE_STRING:
                    if (isset($a_ecs_content->{$target})) {
                        $value = array((string) $a_ecs_content->{$target}, ilECSCategoryMappingRule::ATTR_STRING);
                    } else {
                        $value = "";
                    }
                    break;

                case self::TYPE_TIMEPLACE:
                    if (!is_object($timePlace)) {
                        $timePlace = new ilECSTimePlace();
                        if (isset($a_ecs_content->{$target}) && is_object($a_ecs_content->{$target})) {
                            $timePlace->loadFromJSON($a_ecs_content->{$target});
                        }
                    }
                    switch ($id) {
                        case 'begin':
                        case 'end':
                            $value = array($timePlace->{'getUT' . ucfirst($id)}(),
                                ilECSCategoryMappingRule::ATTR_INT);
                            break;
                            
                        case 'room':
                        case 'cycle':
                            $value = array($timePlace->{'get' . ucfirst($id)}(),
                                ilECSCategoryMappingRule::ATTR_STRING);
                            break;
                    }
                    break;
            }
            
            $res[$id] = $value;
        }
        
        return $res;
    }
    
    /**
     * Get advanced metadata values for object id
     */
    public static function getAdvancedMDValuesForObjId(int $a_obj_id) : array
    {
        $res = array();

        // getting all records
        foreach (ilAdvancedMDValues::getInstancesForObjectId($a_obj_id) as $a_values) {
            // this correctly binds group and definitions
            $a_values->read();
            
            // getting elements for record
            $defs = $a_values->getDefinitions();
            foreach ($a_values->getADTGroup()->getElements() as $element_id => $element) {
                if (!$element->isNull()) {
                    // :TODO: using this for a "flat" presentation
                    $res[$element_id] = $defs[$element_id]->getValueForXML($element);
                } else {
                    // :TODO: is this needed?
                    $res[$element_id] = null;
                }
            }
        }
        
        return $res;
    }
}
