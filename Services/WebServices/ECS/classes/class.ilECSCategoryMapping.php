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
    private static ?array $cached_active_rules = null;

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
        $rules = array();
        $res = $ilDB->query('SELECT mapping_id FROM ecs_container_mapping');
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $rules[] = new ilECSCategoryMappingRule(intval($row->mapping_id));
        }
        return $rules;
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
        foreach (array_keys($references) as $ref_id) {
            if ($tree->getParentId($ref_id) == $cat) {
                $exists = true;
            }
        }
        $ilLog->write(__METHOD__ . ': Creating/Deleting references...');
        
        if (!$exists) {
            $ilLog->write(__METHOD__ . ': Add new reference. STEP 1');
            
            if ($obj_data = ilObjectFactory::getInstanceByRefId($a_ref_id, false)) {
                $obj_data->createReference();
                $obj_data->putInTree($cat);
                $obj_data->setPermissions($cat);
                $ilLog->write(__METHOD__ . ': Add new reference.');
            }
        }
        // Now delete old references
        foreach (array_keys($references) as $ref_id) {
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
        
        $ref_ids = [];
        $res = $ilDB->query("SELECT container_id FROM ecs_container_mapping ");
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ref_ids[] = $row->container_id;
        }
        return $ref_ids;
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
        $course_fields = ilECSUtils::_getOptionalECourseFields();
        foreach ($course_fields as $field) {
            $options[$field] = $lng->txt("obj_rcrs") . " - " . $lng->txt("ecs_field_" . $field);
        }
        
        return $options;
    }
}
