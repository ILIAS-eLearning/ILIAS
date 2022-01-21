<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Stores the applied template id for objects
 * @author  Stefan Meyer <meyer@ilias@gmx.de>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateObjSettings
{

    /**
     * Lookup template id
     * @param int $a_ref_id
     * @return int
     */
    public static function lookupTemplateId(int $a_ref_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT tpl_id FROM didactic_tpl_objs ' .
            'WHERE ref_id = ' . $ilDB->quote($a_ref_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->tpl_id;
        }
        return 0;
    }

    /**
     * Delete by obj id
     * @param int $a_obj_id
     * @return void
     */
    public static function deleteByObjId(int $a_obj_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'DELETE FROM didactic_tpl_objs ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $ilDB->manipulate($query);
    }

    /**
     * Delete by template id
     * @param int $a_tpl_id
     * @return void
     */
    public static function deleteByTemplateId(int $a_tpl_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'DELETE FROM didactic_tpl_objs ' .
            'WHERE tpl_id = ' . $ilDB->quote($a_tpl_id, 'integer');
        $ilDB->manipulate($query);
    }

    /**
     * Delete by ref_id
     * @param int $a_ref_id
     */
    public static function deleteByRefId(int $a_ref_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'DELETE FROM didactic_tpl_objs ' .
            'WHERE ref_id = ' . $ilDB->quote($a_ref_id, 'integer');
        $ilDB->manipulate($query);
    }

    /**
     * Assign template to object
     * @param int $a_obj_id
     * @param int $a_tpl_id
     * @return void
     */
    public static function assignTemplate(int $a_ref_id, int $a_obj_id, int $a_tpl_id) : void
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
     * Lookup template id
     * @param int $a_tpl_id
     * @return array[]
     */
    public static function getAssignmentsByTemplateID(int $a_tpl_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT * FROM didactic_tpl_objs ' .
            'WHERE tpl_id = ' . $ilDB->quote($a_tpl_id, 'integer');
        $res = $ilDB->query($query);
        $assignments = array();

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $assignments[] = array("ref_id" => $row->ref_id, "obj_id" => $row->obj_id);
        }
        return $assignments;
    }

    /**
     * @param int[] $template_ids
     * @return array
     */
    public static function getAssignmentsForTemplates(array $template_ids) : array
    {
        global $DIC;

        $db = $DIC->database();
        $query = 'select * from didactic_tpl_objs ' .
            'where ' . $db->in('tpl_id', $template_ids, false, ilDBConstants::T_INTEGER);
        $res = $db->query($query);
        $assignments = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $assignments[$row->tpl_id][] = $row->ref_id;
        }
        return $assignments;
    }

    /**
     * transfer auto generated flag if source is auto generated
     * @param int $a_src
     * @param int $a_dest
     * @return bool
     */
    public static function transferAutoGenerateStatus(int $a_src, int $a_dest) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT auto_generated FROM didactic_tpl_settings ' .
            'WHERE id = ' . $ilDB->quote($a_src, 'integer');
        $res = $ilDB->query($query);

        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        if ($row->auto_generated == 0) {
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
