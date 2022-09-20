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
 * @author Per Pascal Seeland <pascal.seeland@tik.uni-stuttgart.de>
 */
class ilECSParticipantSettingsRepository
{
    private ilDBInterface $db;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * Get participants which are enabled and export is allowed
     */
    public function getExportableParticipants($a_type): array
    {
        $query = 'SELECT sid,mid,export_types FROM ecs_part_settings ep ' .
            'JOIN ecs_server es ON ep.sid = es.server_id ' .
            'WHERE export = ' . $this->db->quote(1, 'integer') . ' ' .
            'AND active = ' . $this->db->quote(1, 'integer') . ' ' .
            'ORDER BY cname,es.title';

        $res = $this->db->query($query);
        $mids = array();
        $counter = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (in_array($a_type, (array) unserialize($row->export_types, ['allowed_classes' => true]), true)) {
                $mids[$counter]['sid'] = $row->sid;
                $mids[$counter]['mid'] = $row->mid;
                $counter++;
            }
        }
        return $mids;
    }

    /**
     * Get server ids which allow an export
     */
    public function getServersContaingExports(): array
    {
        $query = 'SELECT DISTINCT(sid) FROM ecs_part_settings  ep ' .
            'JOIN ecs_server es ON ep.sid = es.server_id ' .
            'WHERE export = ' . $this->db->quote(1, 'integer') . ' ' .
            'AND active = ' . $this->db->quote(1, 'integer') . ' ';
        $res = $this->db->query($query);
        $sids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $sids[] = $row->sid;
        }
        return $sids;
    }

    // Code below is unused according to eclipse. keep around if not true, otherwise remove
    //     /**
    //      * Delete by server
    //      * @global  $ilDB
    //      * @param int $a_server_id
    //      */
    //     public static function deleteByServer($a_server_id)
    //     {
    //         global $DIC;

    //         $ilDB = $DIC['ilDB'];

    //         $query = 'DELETE from ecs_part_settings ' .
    //             'WHERE sid = ' . $this->db->quote($a_server_id, 'integer');
    //         $this->db->manipulate($query);
    //     }
}
