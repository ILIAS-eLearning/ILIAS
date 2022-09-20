<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Stores the applied template id for objects
 * @author  Stefan Meyer <meyer@ilias@gmx.de>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateObjSettings
{
    public static function lookupTemplateId(int $a_ref_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT tpl_id FROM didactic_tpl_objs ' .
            'WHERE ref_id = ' . $ilDB->quote($a_ref_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->tpl_id;
        }

        return 0;
    }

    public static function deleteByObjId(int $a_obj_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'DELETE FROM didactic_tpl_objs ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $ilDB->manipulate($query);
    }

    public static function deleteByTemplateId(int $a_tpl_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'DELETE FROM didactic_tpl_objs ' .
            'WHERE tpl_id = ' . $ilDB->quote($a_tpl_id, 'integer');
        $ilDB->manipulate($query);
    }

    public static function deleteByRefId(int $a_ref_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'DELETE FROM didactic_tpl_objs ' .
            'WHERE ref_id = ' . $ilDB->quote($a_ref_id, 'integer');
        $ilDB->manipulate($query);
    }

    public static function assignTemplate(int $a_ref_id, int $a_obj_id, int $a_tpl_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        self::deleteByRefId($a_ref_id);

        $query = 'INSERT INTO didactic_tpl_objs (ref_id,obj_id,tpl_id) ' .
            'VALUES ( ' .
            $ilDB->quote($a_ref_id, 'integer') . ', ' .
            $ilDB->quote($a_obj_id, 'integer') . ', ' .
            $ilDB->quote($a_tpl_id, 'integer') . ' ' .
            ')';
        $ilDB->manipulate($query);
    }

    /**
     * @param int $a_tpl_id
     * @return array{ref_id: int, obj_id: int}[]
     */
    public static function getAssignmentsByTemplateID(int $a_tpl_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT * FROM didactic_tpl_objs ' .
            'WHERE tpl_id = ' . $ilDB->quote($a_tpl_id, 'integer');
        $res = $ilDB->query($query);
        $assignments = [];

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $assignments[] = ["ref_id" => (int) $row->ref_id, "obj_id" => (int) $row->obj_id];
        }

        return $assignments;
    }

    /**
     * @param int[] $template_ids
     * @return array<int, int[]>
     */
    public static function getAssignmentsForTemplates(array $template_ids): array
    {
        global $DIC;

        $db = $DIC->database();
        $query = 'select * from didactic_tpl_objs ' .
            'where ' . $db->in('tpl_id', $template_ids, false, ilDBConstants::T_INTEGER);
        $res = $db->query($query);
        $assignments = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $assignments[(int) $row->tpl_id][] = (int) $row->ref_id;
        }

        return $assignments;
    }

    /**
     * Transfer auto generated flag if source is auto generated
     * @param int $a_src
     * @param int $a_dest
     * @return bool
     */
    public static function transferAutoGenerateStatus(int $a_src, int $a_dest): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT auto_generated FROM didactic_tpl_settings ' .
            'WHERE id = ' . $ilDB->quote($a_src, 'integer');
        $res = $ilDB->query($query);

        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        if ((int) $row->auto_generated === 0) {
            return false;
        }

        $query = 'UPDATE didactic_tpl_settings ' .
            'SET ' .
            'auto_generated = ' . $ilDB->quote(1, 'integer') .
            ' WHERE id = ' . $ilDB->quote($a_dest, 'integer');
        $ilDB->manipulate($query);

        $query = 'UPDATE didactic_tpl_settings ' .
            'SET ' .
            'auto_generated = ' . $ilDB->quote(0, 'integer') .
            ' WHERE id = ' . $ilDB->quote($a_src, 'integer');
        $ilDB->manipulate($query);

        return true;
    }
}
