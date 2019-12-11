<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesWebServicesECS
*/

class ilECSUtils
{
    const TYPE_ARRAY = 1;
    const TYPE_INT = 2;
    const TYPE_STRING = 3;
    const TYPE_TIMEPLACE = 4;
        
    /**
     * Lookup participant name
     * @param int	$a_owner	Mid of participant
     * @param int	$a_server_id
     * @return
     */
    public static function lookupParticipantName($a_owner, $a_server_id)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        try {
            include_once './Services/WebServices/ECS/classes/class.ilECSCommunityReader.php';
            $reader = ilECSCommunityReader::getInstanceByServerId($a_server_id);
            if ($part = $reader->getParticipantByMID($a_owner)) {
                return $part->getParticipantName();
            }
            return '';
        } catch (ilECSConnectorException $e) {
            $ilLog->write(__METHOD__ . ': Error reading participants.');
            return '';
        }
    }
    
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
        include_once './Services/WebServices/ECS/classes/class.ilECSCategoryMappingRule.php';
        include_once './Services/WebServices/ECS/classes/class.ilECSCommunitiesCache.php';
        
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
                    $value = array(implode(',', (array) $a_ecs_content->$target), ilECSCategoryMappingRule::ATTR_ARRAY);
                    break;

                case ilECSUtils::TYPE_INT:
                    $value = array((int) $a_ecs_content->$target, ilECSCategoryMappingRule::ATTR_INT);
                    break;

                case ilECSUtils::TYPE_STRING:
                    $value = array((string) $a_ecs_content->$target, ilECSCategoryMappingRule::ATTR_STRING);
                    break;

                case ilECSUtils::TYPE_TIMEPLACE:
                    if (!is_object($timePlace)) {
                        include_once('./Services/WebServices/ECS/classes/class.ilECSTimePlace.php');
                        if (is_object($a_ecs_content->$target)) {
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
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
                                
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
