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

include_once './Services/WebServices/ECS/classes/class.ilECSCategoryMappingRule.php';

/**
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSCategoryMapping
{
    private static $cached_active_rules = null;

    /**
     * get active rules
     *
     * @return array
     * @static
     */
    public static function getActiveRules()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $res = $ilDB->query('SELECT mapping_id FROM ecs_container_mapping');
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $rules[] = new ilECSCategoryMappingRule($row->mapping_id);
        }
        return $rules ? $rules : array();
    }
    
    /**
     * get matching category
     *
     * @param object	$econtent	ilECSEcontent
     * @return
     * @static
     */
    public static function getMatchingCategory($a_server_id, $a_matchable_content)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        if (is_null(self::$cached_active_rules)) {
            self::$cached_active_rules = self::getActiveRules();
        }
        foreach (self::$cached_active_rules as $rule) {
            if ($rule->matches($a_matchable_content)) {
                $ilLog->write(__METHOD__ . ': Found assignment for field type: ' . $rule->getFieldName());
                return $rule->getContainerId();
            }
            $ilLog->write(__METHOD__ . ': Category assignment failed for field: ' . $rule->getFieldName());
        }
        // Return default container
        $ilLog->write(__METHOD__ . ': Using default container');

        return ilECSSetting::getInstanceByServerId($a_server_id)->getImportId();
    }
    
    /**
     * Handle update of ecs content and create references.
     *
     * @return
     * @static
     */
    public static function handleUpdate($a_obj_id, $a_server_id, $a_matchable_content)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilLog = $DIC['ilLog'];
     
        $cat = self::getMatchingCategory($a_server_id, $a_matchable_content);
                    
        $a_ref_id = current(ilObject::_getAllReferences($a_obj_id));
        $references = ilObject::_getAllReferences(ilObject::_lookupObjId($a_ref_id));
        $all_cats = self::lookupHandledCategories();
                
        $exists = false;
        foreach ($references as $ref_id => $null) {
            if ($tree->getParentId($ref_id) == $cat) {
                $exists = true;
            }
        }
        $ilLog->write(__METHOD__ . ': Creating/Deleting references...');
        include_once './Services/Object/classes/class.ilObjectFactory.php';
        
        if (!$exists) {
            $ilLog->write(__METHOD__ . ': Add new reference. STEP 1');
            
            if ($obj_data = ilObjectFactory::getInstanceByRefId($a_ref_id, false)) {
                $new_ref_id = $obj_data->createReference();
                $obj_data->putInTree($cat);
                $obj_data->setPermissions($cat);
                $ilLog->write(__METHOD__ . ': Add new reference.');
            }
        }
        // Now delete old references
        foreach ($references as $ref_id => $null) {
            $parent = $tree->getParentId($ref_id);
            if ($parent == $cat) {
                continue;
            }
            if (!in_array($parent, $all_cats)) {
                continue;
            }
            if ($to_delete = ilObjectFactory::getInstanceByRefId($ref_id)) {
                $to_delete->delete();
                $ilLog->write(__METHOD__ . ': Deleted deprecated reference.');
            }
        }
        return true;
    }
     
    /**
     *
     *
     * @return
     * @static
     */
    public static function lookupHandledCategories()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $res = $ilDB->query("SELECT container_id FROM ecs_container_mapping ");
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ref_ids[] = $row->container_id;
        }
        return $ref_ids ? $ref_ids : array();
    }

    /**
     *
     *
     * @return
     * @static
     */
    public static function getPossibleFields()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $options = array(
            "community" => $lng->txt("ecs_field_community"),
            "part_id" => $lng->txt("ecs_field_part_id"),
            "type" => $lng->txt("type")
        );
        
        // will be handled by server soon?
        
        // only courses for now
        include_once('./Services/WebServices/ECS/classes/class.ilECSUtils.php');
        $course_fields = ilECSUtils::_getOptionalECourseFields();
        foreach ($course_fields as $field) {
            $options[$field] = $lng->txt("obj_rcrs") . " - " . $lng->txt("ecs_field_" . $field);
        }
        
        return $options;
    }
}
