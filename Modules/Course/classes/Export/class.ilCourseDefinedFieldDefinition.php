<?php
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
 * @version $Id$
 * @ingroup Modules/Course
 */
class ilCourseDefinedFieldDefinition
{
    public const IL_CDF_SORT_ID = 'field_id';
    public const IL_CDF_SORT_NAME = 'field_name';
    public const IL_CDF_TYPE_TEXT = 1;
    public const IL_CDF_TYPE_SELECT = 2;

    protected ilDBInterface $db;
    protected ilLanguage $lng;

    private int $obj_id;

    private int $id = 0;
    private string $name = '';
    private int $type = 0;
    private array $values = [];
    private array $value_options = [];
    private bool $required = false;

    public function __construct(int $a_obj_id, int $a_field_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->obj_id = $a_obj_id;
        $this->id = $a_field_id;

        if ($this->id) {
            $this->read();
        }
    }

    public static function _clone(int $a_source_id, int $a_target_id): void
    {
        foreach (ilCourseDefinedFieldDefinition::_getFields($a_source_id) as $field_obj) {
            $cdf = new ilCourseDefinedFieldDefinition($a_target_id);
            $cdf->setName($field_obj->getName());
            $cdf->setType($field_obj->getType());
            $cdf->setValues($field_obj->getValues());
            $cdf->setValueOptions($field_obj->getValueOptions());
            $cdf->enableRequired($field_obj->isRequired());
            $cdf->save();
        }
    }

    public static function _deleteByContainer(int $a_container_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        // Delete user entries
        foreach (ilCourseDefinedFieldDefinition::_getFieldIds($a_container_id) as $field_id) {
            ilCourseUserData::_deleteByField($field_id);
        }
        $query = "DELETE FROM crs_f_definitions " .
            "WHERE obj_id = " . $ilDB->quote($a_container_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    /**
     * Check if there are any define fields
     */
    public static function _hasFields(int $a_container_id): int
    {
        return count(ilCourseDefinedFieldDefinition::_getFields($a_container_id));
    }

    /**
     * Get all fields of a container
     * @param int container obj_id
     * @return ilCourseDefinedFieldDefinition[]
     */
    public static function _getFields(int $a_container_id, $a_sort = self::IL_CDF_SORT_NAME): array
    {
        $fields = [];
        foreach (ilCourseDefinedFieldDefinition::_getFieldIds($a_container_id, self::IL_CDF_SORT_ID) as $field_id) {
            $fields[] = new ilCourseDefinedFieldDefinition($a_container_id, $field_id);
        }
        return $fields;
    }

    /**
     * Get required filed id's
     * @return int[]
     */
    public static function _getRequiredFieldIds(int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM crs_f_definitions " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND field_required = 1";
        $res = $ilDB->query($query);
        $req_fields = [];
        while ($row = $ilDB->fetchObject($res)) {
            $req_fields[] = (int) $row->field_id;
        }
        return $req_fields;
    }

    public static function _fieldsToInfoString(int $a_obj_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT field_name FROM crs_f_definitions " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);
        $fields = [];
        while ($row = $ilDB->fetchObject($res)) {
            $fields[] = $row->field_name;
        }
        return implode('<br />', $fields);
    }

    /**
     * @return int[]
     */
    public static function _getFieldIds(int $a_container_id, string $a_sort = self::IL_CDF_SORT_ID): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT field_id FROM crs_f_definitions " .
            "WHERE obj_id = " . $ilDB->quote($a_container_id, 'integer') . " " .
            "ORDER BY " . self::IL_CDF_SORT_ID;
        $res = $ilDB->query($query);
        $field_ids = [];
        while ($row = $ilDB->fetchObject($res)) {
            $field_ids[] = (int) $row->field_id;
        }
        return $field_ids;
    }

    public static function _lookupName(int $a_field_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT * FROM crs_f_definitions " .
            "WHERE field_id = " . $ilDB->quote($a_field_id, 'integer');

        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return $row->field_name ?: '';
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $a_type): void
    {
        $this->type = $a_type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $a_name): void
    {
        $this->name = $a_name;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValues(array $a_values): void
    {
        $this->values = $a_values;
    }

    public function getValueById(int $a_id): string
    {
        if (
            is_array($this->values) &&
            array_key_exists($a_id, $this->values)
        ) {
            return $this->values[$a_id];
        }
        return '';
    }

    public function getIdByValue(string $a_value): int
    {
        return (($pos = array_search($a_value, $this->values)) === false) ? -1 : $pos;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function enableRequired(bool $a_status): void
    {
        $this->required = $a_status;
    }

    public function setValueOptions(array $a_options): void
    {
        $this->value_options = $a_options;
    }

    public function getValueOptions(): array
    {
        return $this->value_options;
    }

    public function prepareSelectBox(): array
    {
        $options = array();
        $options[''] = $this->lng->txt('select_one');

        foreach ($this->values as $key => $value) {
            $options[$this->getId() . '_' . $key] = $value;
        }
        return $options;
    }

    public function prepareValues(array $a_values): array
    {
        $tmp_values = [];
        $tmp_values = array_filter($a_values, 'strlen');
        return $tmp_values;
    }

    public function appendValues(array $a_values): bool
    {
        $this->values = array_unique(array_merge($this->values, $a_values));
        #sort($this->values);
        return true;
    }

    public function save(): void
    {
        $next_id = $this->db->nextId('crs_f_definitions');
        $query = "INSERT INTO crs_f_definitions (field_id,obj_id,field_name,field_type,field_values,field_required,field_values_opt) " .
            "VALUES ( " .
            $this->db->quote($next_id, 'integer') . ", " .
            $this->db->quote($this->getObjId(), 'integer') . ", " .
            $this->db->quote($this->getName(), "text") . ", " .
            $this->db->quote($this->getType(), 'integer') . ", " .
            $this->db->quote(serialize($this->getValues()), 'text') . ", " .
            $this->db->quote($this->isRequired(), 'integer') . ", " .
            $this->db->quote(serialize($this->getValueOptions()), 'text') . ' ' .
            ") ";
        $res = $this->db->manipulate($query);
        $this->id = $next_id;
    }

    public function update(): void
    {
        $query = "UPDATE crs_f_definitions " .
            "SET field_name = " . $this->db->quote($this->getName(), 'text') . ", " .
            "field_type = " . $this->db->quote($this->getType(), 'integer') . ", " .
            "field_values = " . $this->db->quote(serialize($this->getValues()), 'text') . ", " .
            "field_required = " . $this->db->quote($this->isRequired(), 'integer') . ", " .
            'field_values_opt = ' . $this->db->quote(serialize($this->getValueOptions()), 'text') . ' ' .
            "WHERE field_id = " . $this->db->quote($this->getId(), 'integer') . " " .
            "AND obj_id = " . $this->db->quote($this->getObjId(), 'integer');
        $res = $this->db->manipulate($query);
    }

    public function delete(): void
    {
        ilCourseUserData::_deleteByField($this->getId());
        $query = "DELETE FROM crs_f_definitions " .
            "WHERE field_id = " . $this->db->quote($this->getId(), 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    private function read(): void
    {
        $query = "SELECT * FROM crs_f_definitions " .
            "WHERE field_id = " . $this->db->quote($this->getId(), 'integer') . " " .
            "AND obj_id = " . $this->db->quote($this->getObjId(), 'integer') . " ";

        $res = $this->db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        $this->setName((string) $row->field_name);
        $this->setType((int) $row->field_type);
        $this->setValues(unserialize($row->field_values));
        $this->setValueOptions(unserialize($row->field_values_opt));
        $this->enableRequired((bool) $row->field_required);
    }
}
