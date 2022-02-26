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
    const TYPE_ARRAY = 1;
    const TYPE_INT = 2;
    const TYPE_STRING = 3;
    const TYPE_TIMEPLACE = 4;
        
    /**
     * get optional econtent fields
     * These fields might be mapped against AdvancedMetaData field definitions
     *
     * @access public
     * @static
     *
     */
    public static function _getOptionalEContentFields()
    {
        // :TODO: ?
        $def = self::getEContentDefinition('/campusconnect/courselinks');
        return array_keys($def);
    }
    
    /**
     * get optional econtent fields
     * These fields might be mapped against AdvancedMetaData field definitions
     *
     * @access public
     * @static
     *
     */
    public static function _getOptionalECourseFields()
    {
        // :TODO: ?
        $def = self::getEContentDefinition('/campusconnect/courselinks');
        return array_keys($def);
    }

    /**
     * Get all possible remote object types
     *
     * @param bool $a_with_captions
     * @return array
     */
    public static function getPossibleRemoteTypes($a_with_captions = false)
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
     *
     * @param bool $a_with_captions
     * @return array
     */
    public static function getPossibleReleaseTypes($a_with_captions = false)
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
     *
     * @param string $a_resource_id
     * @return array
     */
    public static function getEContentDefinition($a_resource_id)
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
                return array();
        }
    }
    
    /**
     * Convert ECS content to rule matchable values
     *
     * @param string $a_resource_id
     * @param int $a_server_id
     * @param object $a_ecs_content
     * @param int $a_owner
     * @return array
     */
    public static function getMatchableContent($a_resource_id, $a_server_id, $a_ecs_content, $a_owner)
    {
        // see ilECSCategoryMapping::getPossibleFields();
        $res = array();
        $res["part_id"] = array($a_owner, ilECSCategoryMappingRule::ATTR_INT);
        $res["community"] = array(ilECSCommunitiesCache::getInstance()->lookupTitle($a_server_id, $a_owner),
            ilECSCategoryMappingRule::ATTR_STRING);
        
        $definition = self::getEContentDefinition($a_resource_id);
        
        $timePlace = null;
        foreach ($definition as $id => $type) {
            if (is_array($type)) {
                $target = $type[1];
                $type = $type[0];
            } else {
                $target = $id;
            }
            switch ($type) {
                case ilECSUtils::TYPE_ARRAY:
                    if (isset($a_ecs_content->$target)) {
                        $value = array(implode(',', (array) $a_ecs_content->$target), ilECSCategoryMappingRule::ATTR_ARRAY);
                    } else {
                        $value = [];
                    }
                    break;

                case ilECSUtils::TYPE_INT:
                    if (isset($a_ecs_content->$target)) {
                        $value = array((int) $a_ecs_content->$target, ilECSCategoryMappingRule::ATTR_INT);
                    } else {
                        $value = 0;
                    }
                    break;

                case ilECSUtils::TYPE_STRING:
                    if (isset($a_ecs_content->$target)) {
                        $value = array((string) $a_ecs_content->$target, ilECSCategoryMappingRule::ATTR_STRING);
                    } else {
                        $value = "";
                    }
                    break;

                case ilECSUtils::TYPE_TIMEPLACE:
                    if (!is_object($timePlace)) {
                        if (isset($a_ecs_content->$target) && is_object($a_ecs_content->$target)) {
                            $timePlace = new ilECSTimePlace();
                            $timePlace->loadFromJSON($a_ecs_content->$target);
                        } else {
                            $timePlace = new ilECSTimePlace();
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
     *
     * @param int $a_obj_id
     * @return array
     */
    public static function getAdvancedMDValuesForObjId($a_obj_id)
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
