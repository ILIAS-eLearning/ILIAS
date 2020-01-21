<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignment.php';

/**
 *
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSNodeMappingAssignments
{

    /**
     * Check if there is any assignment for a cms tree
     * @param int $a_server_id
     * @param int $a_tree_id
     */
    public static function hasAssignments($a_server_id, $a_mid, $a_tree_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT ref_id FROM ecs_node_mapping_a ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer') . ' ' .
            'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
            'AND cs_root = ' . $ilDB->quote($a_tree_id, 'integer') . ' ' .
            'AND ref_id > 0';
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return true;
        }
        return false;
    }
    
    /**
     * Lookup Settings
     * @param type $a_server_id
     * @param type $a_mid
     * @param type $a_tree_id
     *
     * @return mixed false in case of no specific setting available, array of settings
     */
    public static function lookupSettings($a_server_id, $a_mid, $a_tree_id, $a_node_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT title_update, position_update, tree_update FROM ecs_node_mapping_a ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer') . ' ' .
            'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
            'AND cs_root = ' . $ilDB->quote($a_tree_id, 'integer') . ' ' .
            'AND cs_id = ' . $ilDB->quote($a_node_id, 'integer');
        $res = $ilDB->query($query);
        
        if (!$res->numRows()) {
            return false;
        }
        
        $settings = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $settings['title_update'] = $row->title_update;
            $settings['position_update'] = $row->position_update;
            $settings['tree_update'] = $row->tree_update;
        }
        return (array) $settings;
    }
    
    /**
     * Lookup assignments
     * @global  $ilDB
     * @param <type> $a_server_id
     * @param <type> $a_mid
     * @param <type> $a_tree_id
     */
    public static function lookupAssignmentIds($a_server_id, $a_mid, $a_tree_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT cs_id FROM ecs_node_mapping_a ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer') . ' ' .
            'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
            'AND cs_root = ' . $ilDB->quote($a_tree_id, 'integer') . ' ' .
            'AND ref_id > 0';
        $res = $ilDB->query($query);
        
        $assignments = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $assignments[] = $row->cs_id;
        }
        return $assignments;
    }

    /**
     * Lookup assignments
     * @param <type> $a_server_id
     * @param <type> $a_mid
     * @param <type> $a_tree_id
     */
    public static function lookupAssignmentsByRefId($a_server_id, $a_mid, $a_tree_id, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT cs_id FROM ecs_node_mapping_a ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer') . ' ' .
            'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
            'AND cs_root = ' . $ilDB->quote($a_tree_id, 'integer') . ' ' .
            'AND ref_id = ' . $ilDB->quote($a_ref_id, 'integer') . ' ';
        $res = $ilDB->query($query);

        $assignments = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $assignments[] = $row->cs_id;
        }
        return $assignments;
    }


    /**
     * Check if whole tree is mapped
     * @param int $a_server_id
     * @param int $a_tree_id
     */
    public static function isWholeTreeMapped($a_server_id, $a_mid, $a_tree_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT depth FROM ecs_node_mapping_a ' .
            'JOIN ecs_cms_tree ON (tree = cs_root AND child = cs_id) ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer') . ' ' .
            'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
            'AND cs_root = ' . $ilDB->quote($a_tree_id, 'integer') . ' ';
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->depth == 1;
        }
        return false;
    }

    /**
     * Lookup default title update setting
     */
    public static function lookupDefaultTitleUpdate($a_server_id, $a_mid, $a_tree_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT title_update FROM ecs_node_mapping_a ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer') . ' ' .
            'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
            'AND cs_root = ' . $ilDB->quote($a_tree_id, 'integer') . ' ' .
            'AND cs_id = ' . $ilDB->quote(0, 'integer') . ' ';
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->title_update;
        }
        return false;
    }

    /**
     * Get cs ids for ref_id
     * @global <type> $ilDB
     * @param <type> $a_server_id
     * @param <type> $a_mid
     * @param <type> $a_tree_id
     * @param <type> $a_ref_id
     * @return <type>
     */
    public static function lookupMappedItemsForRefId($a_server_id, $a_mid, $a_tree_id, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT cs_id FROM ecs_node_mapping_a ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer') . ' ' .
            'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
            'AND cs_root = ' . $ilDB->quote($a_tree_id, 'integer') . ' ' .
            'AND ref_id = ' . $ilDB->quote($a_ref_id, 'integer') . ' ';
        $res = $ilDB->query($query);

        $cs_ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $cs_ids[] = $row->cs_id;
        }
        return $cs_ids;
    }

    /**
     * Delete mappings
     * @global  $ilDB
     * @param <type> $a_server_id
     * @param <type> $a_mid
     * @param <type> $a_tree_id
     * @param <type> $a_ref_id
     * @param <type> $cs_ids
     * @return <type>
     */
    public static function deleteMappingsByCsId($a_server_id, $a_mid, $a_tree_id, $cs_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM ecs_node_mapping_a ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id) . ' ' .
            'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
            'AND cs_root = ' . $ilDB->quote($a_tree_id, 'integer') . ' ' .
            'AND ' . $ilDB->in('cs_id', $cs_ids, false, 'integer');
        $ilDB->manipulate($query);
        return true;
    }

    /**
     * Delete mappings
     * @global $ilDB $ilDB
     * @param <type> $a_server_id
     * @param <type> $a_mid
     * @param <type> $a_tree_id
     * @return <type>
     */
    public static function deleteMappings($a_server_id, $a_mid, $a_tree_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM ecs_node_mapping_a ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id) . ' ' .
            'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
            'AND cs_root = ' . $ilDB->quote($a_tree_id, 'integer') . ' ';
        $ilDB->manipulate($query);
        return true;
    }

    /**
     * delete disconnectable mappings
     * @param <type> $a_server_id
     * @param <type> $a_mid
     * @param <type> $a_tree_id
     */
    public static function deleteDisconnectableMappings($a_server_id, $a_mid, $a_tree_id, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';

        $toDelete = array();
        foreach (self::lookupAssignmentsByRefId($a_server_id, $a_mid, $a_tree_id, $a_ref_id) as $assignment) {
            $status = ilECSCmsData::lookupStatusByCmsId($a_server_id, $a_mid, $a_tree_id, $assignment);

            switch ($status) {
                case ilECSCmsData::MAPPING_UNMAPPED:
                    $toDelete[] = $assignment;
                    break;

                case ilECSCmsData::MAPPING_PENDING_DISCONNECTABLE:
                    $toDelete[] = $assignment;
                    break;
                case ilECSCmsData::MAPPING_PENDING_NOT_DISCONNECTABLE:
                    break;

                case ilECSCmsData::MAPPING_MAPPED:
                    $toDelete[] = $assignment;
                    break;

                case ilECSCmsData::MAPPING_DELETED:
                    $toDelete[] = $assignment;
                    break;
            }
        }
        self::deleteMappingsByCsId($a_server_id, $a_mid, $a_tree_id, $toDelete);
    }
}
