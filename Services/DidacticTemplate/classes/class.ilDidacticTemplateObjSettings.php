<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Stores the applied template id for objects
 *
 * @author Stefan Meyer <meyer@ilias@gmx.de>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateObjSettings
{

    /**
     * Lookup template id
     * @global ilDB $ilDB
     * @param int $a_ref_id
     * @return int
     */
    public static function lookupTemplateId($a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

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
     * @global ilDB $ilDB
     * @param int $a_obj_id
     * @return bool
     */
    public static function deleteByObjId($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM didactic_tpl_objs ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $ilDB->manipulate($query);
        return true;
    }

    /**
     * Delete by template id
     * @global ilDB $ilDB
     * @param int $a_tpl_id
     * @return bool
     */
    public static function deleteByTemplateId($a_tpl_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM didactic_tpl_objs ' .
            'WHERE tpl_id = ' . $ilDB->quote($a_tpl_id, 'integer');
        $ilDB->manipulate($query);
        return true;
    }

    /**
     * Delete by ref_id
     * @global ilDB $ilDB
     * @param int $a_ref_id
     */
    public static function deleteByRefId($a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM didactic_tpl_objs ' .
            'WHERE ref_id = ' . $ilDB->quote($a_ref_id, 'integer');
        $ilDB->manipulate($query);
    }

    /**
     * Assign template to object
     * @global ilDB $ilDB
     * @param int $a_obj_id
     * @param int $a_tpl_id
     * @return bool
     */
    public static function assignTemplate($a_ref_id, $a_obj_id, $a_tpl_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        self::deleteByRefId($a_ref_id);

        $query = 'INSERT INTO didactic_tpl_objs (ref_id,obj_id,tpl_id) ' .
            'VALUES ( ' .
            $ilDB->quote($a_ref_id, 'integer') . ', ' .
            $ilDB->quote($a_obj_id, 'integer') . ', ' .
            $ilDB->quote($a_tpl_id, 'integer') . ' ' .
            ')';
        $ilDB->manipulate($query);
        return true;
    }
    /**
     * Lookup template id
     * @global ilDB $ilDB
     * @param int $a_tpl_id
     * @return array[]
     */
    public static function getAssignmentsByTemplateID($a_tpl_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

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
     * transfer auto generated flag if source is auto generated
     *
     * @param int $a_src
     * @param int $a_dest
     * @return bool
     */
    public static function transferAutoGenerateStatus($a_src, $a_dest)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

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
