<?php declare(strict_types=1);
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
 * ADT Active Record by type helper class
 * This class expects a valid primary for all actions!
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTActiveRecordByType
{
    protected ilADTDBBridge $properties;
    protected string $element_column = '';
    protected string $element_column_type = '';
    protected array $tables_map = [];
    protected array $tables_map_type = [];

    protected static array $preloaded = [];

    public const SINGLE_COLUMN_NAME = "value";

    protected ilDBInterface $db;

    public function __construct(ilADTDBBridge $a_properties)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->properties = $a_properties;
        $this->init();
    }

    protected function init() : void
    {
        $this->tables_map = self::getTablesMap();

        // type to table lookup
        $this->tables_map_type = [];
        foreach ($this->tables_map as $table => $types) {
            foreach ($types as $type) {
                $this->tables_map_type[$type] = $table;
            }
        }
    }

    public function setElementIdColumn(string $a_name, string $a_type) : void
    {
        $this->element_column = $a_name;
        $this->element_column_type = $a_type;
    }

    public function getElementIdColumn() : string
    {
        return $this->element_column;
    }

    protected static function getTablesMap() : array
    {
        return [
            'text' => ['Text'],
            'enum' => ['Enum', 'MultiEnum'],
            "int" => ["Integer"],
            "float" => ["Float"],
            "date" => ["Date"],
            "datetime" => ["DateTime"],
            "location" => ["Location"],
            'extlink' => ['ExternalLink'],
            'intlink' => ['InternalLink'],
            'ltext' => ['LocalizedText']
        ];
    }

    /**
     * Get table name for ADT type
     * @param string $a_type
     * @return string
     */
    protected function getTableForElementType(string $a_type) : string
    {
        if (isset($this->tables_map_type[$a_type])) {
            return $this->properties->getTable() . "_" . $this->tables_map_type[$a_type];
        }
        return '';
    }

    /**
     * Map all group elements to sub tables
     * @return array
     */
    protected function mapElementsToTables() : array
    {
        $res = [];
        foreach ($this->properties->getElements() as $element_id => $element) {
            $table = $this->getTableForElementType($element->getADT()->getType());
            if ($table) {
                $res[$table][] = $element_id;
            }
        }
        return $res;
    }

    protected function processTableRowForElement(string $a_sub_table, string $a_element_id, array $a_row) : array
    {
        switch ($a_sub_table) {
            case "location":
                return [
                    $a_element_id . "_lat" => $a_row["loc_lat"],
                    $a_element_id . "_long" => $a_row["loc_long"],
                    $a_element_id . "_zoom" => $a_row["loc_zoom"]
                ];

            case 'extlink':
                return [
                    $a_element_id . '_value' => $a_row['value'],
                    $a_element_id . '_title' => $a_row['title']
                ];

            case 'ltext':
                return [
                    $a_element_id . '_language' => $a_row['value_index'],
                    $a_element_id . '_translation' => $a_row['value']
                ];

            case 'enum':
                return [
                    $a_element_id => $a_row['value_index']
                ];

            default:
                if ($a_row[self::SINGLE_COLUMN_NAME] !== null) {
                    return [$a_element_id => $a_row[self::SINGLE_COLUMN_NAME]];
                }
                break;
        }
        return [];
    }

    /**
     * Read record
     * @param bool $a_return_additional_data
     * @return bool | array
     */
    public function read(bool $a_return_additional_data = false)
    {
        // reset all group elements
        $this->properties->getADT()->reset();

        //  using preloaded data
        // TODO: remove this hack.
        if (is_array(self::$preloaded) && !$a_return_additional_data) {
            $primary = $this->properties->getPrimary();
            foreach (self::$preloaded as $table => $data) {
                $sub_table = '';
                $sub_tables = explode('_', $table);
                if ($sub_tables !== false) {
                    $sub_table = array_pop($sub_tables);
                }
                foreach ($data as $row) {
                    // match by primary key
                    foreach ($primary as $primary_field => $primary_value) {
                        if ($row[$primary_field] != $primary_value[1]) {
                            continue 2;
                        }
                    }

                    $element_id = $row[$this->getElementIdColumn()];
                    if ($this->properties->getADT()->hasElement($element_id)) {
                        $element_row = $this->processTableRowForElement($sub_table, $element_id, $row);
                        if (is_array($element_row)) {
                            $this->properties->getElement($element_id)->readRecord($element_row);
                        }
                    }
                }
            }
        }

        $has_data = false;
        $additional = [];

        // read minimum tables
        foreach ($this->mapElementsToTables() as $table => $element_ids) {
            $sql = "SELECT * FROM " . $table .
                " WHERE " . $this->properties->buildPrimaryWhere();
            $set = $this->db->query($sql);
            if ($this->db->numRows($set)) {
                $sub_table = '';
                $sub_tables = explode('_', $table);
                if ($sub_tables !== false) {
                    $sub_table = array_pop($sub_tables);
                }
                while ($row = $this->db->fetchAssoc($set)) {
                    $element_id = $row[$this->getElementIdColumn()];
                    if (in_array($element_id, $element_ids)) {
                        $has_data = true;

                        $element_row = $this->processTableRowForElement($sub_table, $element_id, $row);
                        if (is_array($element_row)) {
                            $this->properties->getElement($element_id)->readRecord($element_row);
                        }

                        if ($a_return_additional_data) {
                            // removing primary and field id
                            foreach (array_keys($this->properties->getPrimary()) as $key) {
                                unset($row[$key]);
                            }
                            unset($row[$this->getElementIdColumn()]);
                            $additional[$element_id] = $row;
                        }
                    } else {
                        // :TODO: element no longer valid - delete?
                    }
                }
            }
        }

        if ($a_return_additional_data) {
            return $additional;
        }
        return $has_data;
    }

    /**
     * Create/insert record
     * @param array $a_additional_data
     */
    public function write(array $a_additional_data = null) : void
    {
        // find existing entries
        $existing = [];
        foreach (array_keys($this->mapElementsToTables()) as $table) {
            $sql = "SELECT " . $this->getElementIdColumn() . " FROM " . $table .
                " WHERE " . $this->properties->buildPrimaryWhere();
            $set = $this->db->query($sql);
            while ($row = $this->db->fetchAssoc($set)) {
                $id = $row[$this->getElementIdColumn()];

                // leave other records alone
                if ($this->properties->getADT()->hasElement($id)) {
                    $existing[$table][$id] = $id;
                }
            }
        }

        // gather ADT values and distribute by sub-table
        $tmp = [];
        foreach ($this->properties->getElements() as $element_id => $element) {
            if (!$element->getADT()->isNull()) {
                $table = $this->getTableForElementType($element->getADT()->getType());
                if ($table) {
                    $fields = array();
                    $element->prepareUpdate($fields);

                    // @todo add configuration for types not supporting default 'value' column
                    // DONE
                    if ($element->supportsDefaultValueColumn()) {
                        $tmp[$table][$element_id][self::SINGLE_COLUMN_NAME] = $fields[$element_id];
                    } else {
                        $tmp[$table][$element_id] = [];
                        foreach ($fields as $key => $value) {
                            $key = substr((string) $key, strlen((string) $element_id) + 1);
                            // @todo other implementation required
                            if (substr($table, -8) == "location") {
                                // long is reserved word
                                $key = "loc_" . $key;
                            }
                            if (substr($table, -4) == 'enum') {
                                $key = 'value_index';
                            }
                            $tmp[$table][$element_id][$key] = $value;
                        }
                    }

                    if (isset($a_additional_data[$element_id])) {
                        $tmp[$table][$element_id] = array_merge(
                            $tmp[$table][$element_id],
                            $a_additional_data[$element_id]
                        );
                    }
                }
            }
        }

        // update/insert in sub tables
        if (count($tmp)) {
            foreach ($tmp as $table => $elements) {
                foreach ($elements as $element_id => $fields) {
                    if (is_array($fields) && count($fields)) {
                        $current_db_bridge = $this->findCurrentDBBridge($element_id);
                        if (isset($existing[$table][$element_id])) {
                            // update
                            $primary = array_merge(
                                $this->properties->getPrimary(),
                                $current_db_bridge->getAdditionalPrimaryFields()
                            );
                            $primary[$this->getElementIdColumn()] = array($this->element_column_type, $element_id);
                            $this->db->update($table, $fields, $primary);
                        } else {
                            // insert
                            $fields[$this->getElementIdColumn()] = array($this->element_column_type, $element_id);
                            $fields = array_merge(
                                $this->properties->getPrimary(),
                                $current_db_bridge->getAdditionalPrimaryFields(),
                                $fields
                            );
                            $this->db->insert($table, $fields);
                        }
                    }
                    $this->properties->afterUpdateElement(
                        ilDBConstants::T_INTEGER,
                        'field_id',
                        (int) $element_id
                    );

                    if (isset($existing[$table][$element_id])) {
                        unset($existing[$table][$element_id]);
                    }
                }
            }
        }
        // remove all existing values that are now null
        if (count($existing)) {
            foreach ($existing as $table => $element_ids) {
                if ($element_ids) {
                    $this->db->manipulate(
                        $q = "DELETE FROM " . $table .
                            " WHERE " . $this->properties->buildPrimaryWhere() .
                            " AND " . $this->db->in(
                                $this->getElementIdColumn(),
                                $element_ids,
                                false,
                                $this->element_column_type
                            )
                    );
                }
            }
        }
    }

    protected function findCurrentDBBridge(int $element_id) : ?ilADTDBBridge
    {
        foreach ($this->properties->getElements() as $prop_element_id => $prop_element) {
            if ($element_id === $prop_element_id) {
                return $prop_element;
            }
        }
        return null;
    }

    protected static function buildPartialPrimaryWhere(array $a_primary) : string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $where = [];
        foreach ($a_primary as $field => $def) {
            if (!is_array($def[1])) {
                $where[] = $field . "=" . $ilDB->quote($def[1], $def[0]);
            } else {
                $where[] = $ilDB->in($field, $def[1], false, $def[0]);
            }
        }
        if (count($where)) {
            return implode(" AND ", $where);
        }
        return '';
    }

    public static function deleteByPrimary(string $a_table, array $a_primary, string $a_type = null) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $where = self::buildPartialPrimaryWhere($a_primary);
        if (!$where) {
            return;
        }

        // all tables
        if (!$a_type) {
            foreach (array_keys(self::getTablesMap()) as $table) {
                $sql = "DELETE FROM " . $a_table . "_" . $table .
                    " WHERE " . $where;
                $ilDB->manipulate($sql);
            }
        } else {
            $found = null;
            foreach (self::getTablesMap() as $table => $types) {
                if (in_array($a_type, $types)) {
                    $found = $table;
                    break;
                }
            }
            if ($found) {
                $sql = "DELETE FROM " . $a_table . "_" . $found .
                    " WHERE " . $where;
                $ilDB->manipulate($sql);
            }
        }
    }

    public static function preloadByPrimary(string $a_table, array $a_primary) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $where = self::buildPartialPrimaryWhere($a_primary);
        if (!$where) {
            return false;
        }

        self::$preloaded = [];
        foreach (array_keys(self::getTablesMap()) as $table) {
            $sql = "SELECT * FROM " . $a_table . "_" . $table .
                " WHERE " . $where;
            $set = $ilDB->query($sql);
            while ($row = $ilDB->fetchAssoc($set)) {
                self::$preloaded[$table][] = $row;
            }
        }
        return true;
    }

    protected static function getTableTypeMap() : array
    {
        return array(
            "text" => "text",
            "int" => "integer",
            "float" => "float",
            "date" => "date",
            "datetime" => "timestamp"
        );
    }

    /**
     * Clone values by (partial) primary key
     * @param string $a_table
     * @param array  $a_primary_def
     * @param array  $a_source_primary
     * @param array  $a_target_primary
     * @param array  $a_additional
     * @return bool
     */
    public static function cloneByPrimary(
        string $a_table,
        array $a_primary_def,
        array $a_source_primary,
        array $a_target_primary,
        array $a_additional = null
    ) : bool {
        global $DIC;

        $ilDB = $DIC->database();

        $where = self::buildPartialPrimaryWhere($a_source_primary);
        if (!$where) {
            return false;
        }

        $has_data = false;
        $type_map = self::getTableTypeMap();
        foreach (array_keys(self::getTablesMap()) as $table) {
            $sub_table = $a_table . "_" . $table;

            $sql = "SELECT * FROM " . $sub_table .
                " WHERE " . $where;
            $set = $ilDB->query($sql);
            if ($ilDB->numRows($set)) {
                $has_data = true;
                while ($row = $ilDB->fetchAssoc($set)) {
                    // primary fields
                    $fields = array();
                    foreach ($a_primary_def as $pfield => $ptype) {
                        // make source to target primary
                        if (array_key_exists($pfield, $a_target_primary)) {
                            $row[$pfield] = $a_target_primary[$pfield][1];
                        }
                        $fields[$pfield] = array($ptype, $row[$pfield]);
                    }

                    // value field(s)
                    switch ($table) {
                        case "location":
                            $fields["loc_lat"] = ["float", $row["loc_lat"]];
                            $fields["loc_long"] = ["float", $row["loc_long"]];
                            $fields["loc_zoom"] = ["integer", $row["loc_zoom"]];
                            break;

                        case 'ltext':
                            $fields['value_index'] = [ilDBConstants::T_TEXT, $row['value_index']];
                            $fields['value'] = [ilDBConstants::T_TEXT, $row['value']];
                            break;

                        case 'enum':
                            $fields['value_index'] = [ilDBConstants::T_INTEGER, $row['value_index']];
                            break;

                        default:
                            $fields[self::SINGLE_COLUMN_NAME] = array($type_map[$table],
                                                                      $row[self::SINGLE_COLUMN_NAME]
                            );
                            break;
                    }

                    // additional data
                    if ($a_additional) {
                        foreach ($a_additional as $afield => $atype) {
                            $fields[$afield] = array($atype, $row[$afield]);
                        }
                    }
                    $ilDB->insert($sub_table, $fields);
                }
            }
        }
        return $has_data;
    }

    /**
     * Read directly
     * @param string        $a_table
     * @param array         $a_primary
     * @param string | null $a_type
     * @return array|void
     */
    public static function readByPrimary(string $a_table, array $a_primary, ?string $a_type = null) : ?array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $where = self::buildPartialPrimaryWhere($a_primary);
        if (!$where) {
            return null;
        }

        // all tables
        $res = [];
        if (!$a_type) {
            foreach (array_keys(self::getTablesMap()) as $table) {
                $sql = "SELECT * FROM " . $a_table . "_" . $table .
                    " WHERE " . $where;
                $set = $ilDB->query($sql);
                while ($row = $ilDB->fetchAssoc($set)) {
                    $res[] = $row;
                }
            }
        } // type-specific table
        else {
            $found = null;
            foreach (self::getTablesMap() as $table => $types) {
                if (in_array($a_type, $types)) {
                    $found = $table;
                    break;
                }
            }
            if ($found) {
                $sql = "SELECT * FROM " . $a_table . "_" . $found .
                    " WHERE " . $where;
                $set = $ilDB->query($sql);
                while ($row = $ilDB->fetchAssoc($set)) {
                    $res[] = $row;
                }
            }
        }
        return $res;
    }

    public static function create(string $table, array $fields, string $type) : void
    {
        global $DIC;

        $db = $DIC->database();

        $type_table_name = '';
        foreach (self::getTablesMap() as $type_table_part => $types) {
            if (in_array($type, $types)) {
                $type_table_name = $type_table_part;
                break;
            }
        }
        if (!strlen($type_table_name)) {
            return;
        }
        $table_name = $table . '_' . $type_table_name;

        $insert = 'insert into ' . $table_name . ' ( ';
        $cols = [];
        foreach ($fields as $col => $field_definition) {
            $cols[] = $col;
        }
        $insert .= implode(',', $cols);
        $insert .= ') VALUES ( ';
        $values = [];
        foreach ($fields as $col => $field_definition) {
            $values[] = $db->quote($field_definition[1], $field_definition[0]);
        }
        $insert .= implode(',', $values);
        $insert .= ' )';

        $db->manipulate($insert);
    }

    /**
     * Write directly
     * @param string $a_table
     * @param array  $a_primary
     * @param string $a_type
     * @param        $a_value
     */
    public static function writeByPrimary(string $a_table, array $a_primary, string $a_type, $a_value) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $where = self::buildPartialPrimaryWhere($a_primary);
        if (!$where) {
            return;
        }

        // type-specific table
        $found = null;
        foreach (self::getTablesMap() as $table => $types) {
            if (in_array($a_type, $types)) {
                $found = $table;
                break;
            }
        }
        if ($found) {
            $type_map = self::getTableTypeMap();
            $value_col = self::SINGLE_COLUMN_NAME;
            if ($found == 'enum') {
                $value_col = 'value_index';
            }
            $sql = "UPDATE " . $a_table . "_" . $found .
                " SET " . $value_col . "=" . $ilDB->quote($a_value, $type_map[$found]) .
                " WHERE " . $where;
            $ilDB->manipulate($sql);
        }
    }

    /**
     * Find entries
     * @param string $a_table
     * @param string $a_type
     * @param int    $a_field_id
     * @param string $a_condition
     * @param string $a_additional_fields
     * @return array
     */
    public static function find(
        string $a_table,
        string $a_type,
        int $a_field_id,
        string $a_condition,
        array $a_additional_fields = null
    ) : ?array {
        global $DIC;

        $ilDB = $DIC->database();
        // type-specific table
        $found = null;
        foreach (self::getTablesMap() as $table => $types) {
            if (in_array($a_type, $types)) {
                $found = $table;
                break;
            }
        }
        if ($found) {
            $objects = [];
            $sql = "SELECT *" . $a_additional_fields .
                " FROM " . $a_table . "_" . $found .
                " WHERE field_id = " . $ilDB->quote($a_field_id, "integer") .
                " AND " . $a_condition;
            $res = $ilDB->query($sql);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
                $objects[] = $row;
            }
            return $objects;
        }
        return null;
    }
}
