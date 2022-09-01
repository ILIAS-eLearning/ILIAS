<?php

declare(strict_types=0);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesCourse
 */
class ilCourseUserData
{
    private int $user_id;
    private int $field_id;
    private string $value;

    protected ilDBInterface $db;

    public function __construct(int $a_user_id, int $a_field_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user_id = $a_user_id;
        $this->field_id = $a_field_id;
        if ($this->field_id) {
            $this->read();
        }
    }

    public static function _getValuesByObjId(int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $field_ids = ilCourseDefinedFieldDefinition::_getFieldIds($a_obj_id);
        if ($field_ids === []) {
            return array();
        }
        $where = "WHERE " . $ilDB->in('field_id', $field_ids, false, 'integer');
        $query = "SELECT * FROM crs_user_data " .
            $where;

        $res = $ilDB->query($query);
        $user_data = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $user_data[(int) $row->usr_id][(int) $row->field_id] = $row->value;
        }
        return $user_data;
    }

    public static function _checkRequired(int $a_usr_id, int $a_obj_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $required = ilCourseDefinedFieldDefinition::_getRequiredFieldIds($a_obj_id);
        if ($required === []) {
            return true;
        }

        //$and = ("AND field_id IN (".implode(",",ilUtil::quoteArray($required)).")");
        $and = "AND " . $ilDB->in('field_id', $required, false, 'integer');

        $query = "SELECT COUNT(*) num_entries FROM crs_user_data " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "AND value != '' AND value IS NOT NULL " .
            $and . " " .
            " ";
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return $row->num_entries == count($required);
    }

    public static function _deleteByUser(int $a_user_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "DELETE FROM crs_user_data " .
            "WHERE usr_id = " . $ilDB->quote($a_user_id, 'integer');
        $res = $ilDB->manipulate($query);
    }

    public static function _deleteByField(int $a_field_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM crs_user_data " .
            "WHERE field_id = " . $ilDB->quote($a_field_id, 'integer');
        $res = $ilDB->manipulate($query);
    }

    public function setValue(string $a_value): void
    {
        $this->value = $a_value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function update(): void
    {
        $this->delete();
        $this->create();
    }

    public function delete(): void
    {
        $query = "DELETE FROM crs_user_data " .
            "WHERE usr_id = " . $this->db->quote($this->user_id, 'integer') . " " .
            "AND field_id = " . $this->db->quote($this->field_id, 'integer');
        $res = $this->db->manipulate($query);
    }

    public function create(): void
    {
        $query = "INSERT INTO crs_user_data (value,usr_id,field_id) " .
            "VALUES( " .
            $this->db->quote($this->getValue(), 'text') . ", " .
            $this->db->quote($this->user_id, 'integer') . ", " .
            $this->db->quote($this->field_id, 'integer') . " " .
            ")";

        $res = $this->db->manipulate($query);
    }

    private function read(): void
    {
        $query = "SELECT * FROM crs_user_data " .
            "WHERE usr_id = " . $this->db->quote($this->user_id, 'integer') . " " .
            "AND field_id = " . $this->db->quote($this->field_id, 'integer');
        $res = $this->db->query($query);
        $this->setValue('');
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setValue((string) $row->value);
        }
    }
}
