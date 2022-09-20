<?php

declare(strict_types=1);

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
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSNodeMappingAssignments
{
    /**
     * Check if there is any assignment for a cms tree
     */
    public static function hasAssignments(int $a_server_id, int $a_mid, int $a_tree_id): bool
    {
        global $DIC;

        /**
         * @var ilDBInterface $ilDB
         */
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT ref_id FROM ecs_node_mapping_a ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer') . ' ' .
            'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
            'AND cs_root = ' . $ilDB->quote($a_tree_id, 'integer') . ' ' .
            'AND ref_id > 0';
        $res = $ilDB->query($query);
        return $res->rowCount() > 0;
    }

    /**
     * Lookup Settings
     *
     * @return array|false false in case of no specific setting available, array of settings
     */
    public static function lookupSettings(int $a_server_id, int $a_mid, int $a_tree_id, int $a_node_id)
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

        $settings = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $settings['title_update'] = $row->title_update;
            $settings['position_update'] = $row->position_update;
            $settings['tree_update'] = $row->tree_update;
        }
        return $settings;
    }

    /**
     * Lookup assignments
     */
    public static function lookupAssignmentIds(int $a_server_id, int $a_mid, int $a_tree_id): array
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
     */
    public static function lookupAssignmentsByRefId(int $a_server_id, int $a_mid, int $a_tree_id, int $a_ref_id): array
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
     */
    public static function isWholeTreeMapped(int $a_server_id, int $a_mid, int $a_tree_id): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT depth FROM ecs_node_mapping_a ' .
            'JOIN ecs_cms_tree ON (tree = cs_root AND child = cs_id) ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer') . ' ' .
            'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
            'AND cs_root = ' . $ilDB->quote($a_tree_id, 'integer') . ' ';
        $res = $ilDB->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->depth === 1;
        }
        return false;
    }

    /**
     * Lookup default title update setting
     */
    public static function lookupDefaultTitleUpdate($a_server_id, $a_mid, $a_tree_id): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT title_update FROM ecs_node_mapping_a ' .
            'WHERE server_id = ' . $ilDB->quote($a_server_id, 'integer') . ' ' .
            'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
            'AND cs_root = ' . $ilDB->quote($a_tree_id, 'integer') . ' ' .
            'AND cs_id = ' . $ilDB->quote(0, 'integer') . ' ';
        $res = $ilDB->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->title_update;
        }
        return false;
    }

    /**
     * Get cs ids for ref_id
     */
    public static function lookupMappedItemsForRefId(int $a_server_id, int $a_mid, int $a_tree_id, int $a_ref_id): array
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
     */
    public static function deleteMappingsByCsId(int $a_server_id, int $a_mid, int $a_tree_id, array $cs_ids): bool
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
     */
    public static function deleteMappings(int $a_server_id, int $a_mid, int $a_tree_id): bool
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
     */
    public static function deleteDisconnectableMappings(int $a_server_id, int $a_mid, int $a_tree_id, int $a_ref_id): void
    {
        $toDelete = array();
        foreach (self::lookupAssignmentsByRefId($a_server_id, $a_mid, $a_tree_id, $a_ref_id) as $assignment) {
            $status = ilECSCmsData::lookupStatusByCmsId($a_server_id, $a_mid, $a_tree_id, $assignment);

            switch ($status) {
                case ilECSCmsData::MAPPING_PENDING_DISCONNECTABLE:
                case ilECSCmsData::MAPPING_MAPPED:
                case ilECSCmsData::MAPPING_DELETED:
                case ilECSCmsData::MAPPING_UNMAPPED:
                    $toDelete[] = $assignment;
                    break;

                case ilECSCmsData::MAPPING_PENDING_NOT_DISCONNECTABLE:
                    break;
            }
        }
        self::deleteMappingsByCsId($a_server_id, $a_mid, $a_tree_id, $toDelete);
    }
}
