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

const UDF_TYPE_TEXT = 1;
const UDF_TYPE_SELECT = 2;
const UDF_TYPE_WYSIWYG = 3;
const UDF_NO_VALUES = 1;
const UDF_DUPLICATE_VALUES = 2;

/**
 * Additional user data fields definition
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilUserDefinedFields
{
    protected bool $field_certificate = false;
    protected bool $field_group_export = false;
    protected bool $field_course_export = false;
    protected bool $field_export = false;
    protected bool $field_searchable = false;
    protected bool $field_required = false;
    protected bool $field_changeable_lua = false;
    protected bool $field_changeable = false;
    protected bool $field_visib_lua = false;
    protected array $field_values = []; // Missing array type.
    protected int $field_type = 0;
    protected string $field_name = "";
    protected bool $field_visible = false;
    public ?ilDBInterface $db = null;
    /**
     * @var array<int,array<string,mixed>>
     */
    public array $definitions = array();
    private int $field_visible_registration = 0;

    private function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->__read();
    }

    public static function _getInstance(): self
    {
        static $udf = null;

        if (!is_object($udf)) {
            return $udf = new ilUserDefinedFields();
        }
        return $udf;
    }

    public function fetchFieldIdFromImportId(string $a_import_id): int
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        if (!strlen($a_import_id)) {
            return 0;
        }
        $parts = explode('_', $a_import_id);

        if ($parts[0] != 'il') {
            return 0;
        }
        if ($parts[1] != $ilSetting->get('inst_id', '0')) {
            return 0;
        }
        if ($parts[2] != 'udf') {
            return 0;
        }
        if ($parts[3]) {
            // Check if field exists
            if (is_array($this->definitions[$parts[3]])) {
                return $parts[3];
            }
        }
        return 0;
    }

    public function fetchFieldIdFromName(string $a_name): int
    {
        foreach ($this->definitions as $definition) {
            if ($definition['field_name'] == $a_name) {
                return $definition['field_id'];
            }
        }
        return 0;
    }

    public function getDefinitions(): array // Missing array type.
    {
        return $this->definitions ?: array();
    }

    public function getDefinition(int $a_id): array // Missing array type.
    {
        return $this->definitions[$a_id] ?? array();
    }

    public function getVisibleDefinitions(): array // Missing array type.
    {
        $visible_definition = [];
        foreach ($this->definitions as $id => $definition) {
            if ($definition['visible']) {
                $visible_definition[$id] = $definition;
            }
        }
        return $visible_definition;
    }

    public function getLocalUserAdministrationDefinitions(): array // Missing array type.
    {
        $visible_definition = [];
        foreach ($this->definitions as $id => $definition) {
            if ($definition['visib_lua']) {
                $visible_definition[$id] = $definition;
            }
        }
        return $visible_definition;
    }

    public function getChangeableLocalUserAdministrationDefinitions(): array // Missing array type.
    {
        $visible_definition = [];
        foreach ($this->definitions as $id => $definition) {
            if ($definition['changeable_lua']) {
                $visible_definition[$id] = $definition;
            }
        }
        return $visible_definition;
    }

    public function getRegistrationDefinitions(): array // Missing array type.
    {
        $visible_definition = [];
        foreach ($this->definitions as $id => $definition) {
            if ($definition['visib_reg']) {
                $visible_definition[$id] = $definition;
            }
        }
        return $visible_definition;
    }

    public function getSearchableDefinitions(): array // Missing array type.
    {
        $searchable_definition = [];
        foreach ($this->definitions as $id => $definition) {
            if ($definition['searchable']) {
                $searchable_definition[$id] = $definition;
            }
        }
        return $searchable_definition;
    }

    public function getRequiredDefinitions(): array // Missing array type.
    {
        $required_definition = [];
        foreach ($this->definitions as $id => $definition) {
            if ($definition['required']) {
                $required_definition[$id] = $definition;
            }
        }
        return $required_definition;
    }

    public function getCourseExportableFields(): array // Missing array type.
    {
        $cexp_definition = [];
        foreach ($this->definitions as $id => $definition) {
            if ($definition['course_export']) {
                $cexp_definition[$id] = $definition;
            }
        }
        return $cexp_definition;
    }

    public function getGroupExportableFields(): array // Missing array type.
    {
        $cexp_definition = [];
        foreach ($this->definitions as $id => $definition) {
            if ($definition['group_export']) {
                $cexp_definition[$id] = $definition;
            }
        }
        return $cexp_definition;
    }

    /**
     * Get exportable field
     */
    public function getExportableFields(int $a_obj_id): array // Missing array type.
    {
        if (ilObject::_lookupType($a_obj_id) == 'crs') {
            return $this->getCourseExportableFields();
        }
        if (ilObject::_lookupType($a_obj_id) == 'grp') {
            return $this->getGroupExportableFields();
        }
        return array();
    }


    public function setFieldName(string $a_name): void
    {
        $this->field_name = $a_name;
    }

    public function getFieldName(): string
    {
        return $this->field_name;
    }

    public function setFieldType(int $a_type): void
    {
        $this->field_type = $a_type;
    }

    public function isPluginType(): bool
    {
        if (!$this->field_type) {
            return false;
        }
        switch ($this->field_type) {
            case UDF_TYPE_TEXT:
            case UDF_TYPE_SELECT:
            case UDF_TYPE_WYSIWYG:
                return false;

            default:
                return true;
        }
    }

    public function getFieldType(): int
    {
        return $this->field_type;
    }

    /**
     * @param array $a_values<mixed, mixed>
     */
    public function setFieldValues(array $a_values): void
    {
        $this->field_values = array();
        foreach ($a_values as $value) {
            if (strlen($value)) {
                $this->field_values[] = $value;
            }
        }
    }

    public function getFieldValues(): array // Missing array type.
    {
        return $this->field_values ?: array();
    }

    public function enableVisible(bool $a_visible): void
    {
        $this->field_visible = $a_visible;
    }

    public function enabledVisible(): bool
    {
        return $this->field_visible;
    }

    public function enableVisibleLocalUserAdministration(bool $a_visible): void
    {
        $this->field_visib_lua = $a_visible;
    }

    public function enabledVisibleLocalUserAdministration(): bool
    {
        return $this->field_visib_lua;
    }

    public function enableChangeable(bool $a_changeable): void
    {
        $this->field_changeable = $a_changeable;
    }

    public function enabledChangeable(): bool
    {
        return $this->field_changeable;
    }

    public function enableChangeableLocalUserAdministration(bool $a_changeable): void
    {
        $this->field_changeable_lua = $a_changeable;
    }

    public function enabledChangeableLocalUserAdministration(): bool
    {
        return $this->field_changeable_lua;
    }

    public function enableRequired(bool $a_required): void
    {
        $this->field_required = $a_required;
    }

    public function enabledRequired(): bool
    {
        return $this->field_required;
    }

    public function enableSearchable(bool $a_searchable): void
    {
        $this->field_searchable = $a_searchable;
    }

    public function enabledSearchable(): bool
    {
        return $this->field_searchable;
    }

    public function enableExport(bool $a_export): void
    {
        $this->field_export = $a_export;
    }

    public function enabledExport(): bool
    {
        return $this->field_export;
    }

    public function enableCourseExport(bool $a_course_export): void
    {
        $this->field_course_export = $a_course_export;
    }

    public function enabledCourseExport(): bool
    {
        return $this->field_course_export;
    }

    public function enableGroupExport(bool $a_group_export): void
    {
        $this->field_group_export = $a_group_export;
    }

    public function enabledGroupExport(): bool
    {
        return $this->field_group_export;
    }

    public function enableCertificate(bool $a_c): void
    {
        $this->field_certificate = $a_c;
    }

    public function enabledCertificate(): bool
    {
        return $this->field_certificate;
    }

    public function enableVisibleRegistration(bool $a_visible_registration): void
    {
        $this->field_visible_registration = $a_visible_registration;
    }

    public function enabledVisibleRegistration(): bool
    {
        return $this->field_visible_registration;
    }

    public function fieldValuesToSelectArray(
        array $a_values,
        bool $a_with_selection_info = true
    ): array {
        global $DIC;

        $lng = $DIC->language();
        $values = [];
        if ($a_with_selection_info) {
            $values[''] = $lng->txt('please_select');
        }
        foreach ($a_values as $value) {
            $values[$value] = $value;
        }
        if (count($values) > (int) $a_with_selection_info) {
            return $values;
        }
        return [];
    }

    public function validateValues(): int
    {
        $number = 0;
        $unique = array();
        foreach ($this->getFieldValues() as $value) {
            if (!strlen($value)) {
                continue;
            }
            $number++;
            $unique[$value] = $value;
        }

        if (!count($unique)) {
            return UDF_NO_VALUES;
        }
        if ($number != count($unique)) {
            return UDF_DUPLICATE_VALUES;
        }
        return 0;
    }

    public function nameExists(string $a_field_name): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM udf_definition " .
            "WHERE field_name = " . $this->db->quote($a_field_name, 'text') . " ";
        $res = $ilDB->query($query);

        return (bool) $res->numRows();
    }

    public function add(): int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // Add definition entry
        $next_id = $ilDB->nextId('udf_definition');

        $values = array(
            'field_id' => array('integer',$next_id),
            'field_name' => array('text',$this->getFieldName()),
            'field_type' => array('integer', $this->getFieldType()),
            'field_values' => array('clob',serialize($this->getFieldValues())),
            'visible' => array('integer', (int) $this->enabledVisible()),
            'changeable' => array('integer', (int) $this->enabledChangeable()),
            'required' => array('integer', (int) $this->enabledRequired()),
            'searchable' => array('integer', (int) $this->enabledSearchable()),
            'export' => array('integer', (int) $this->enabledExport()),
            'course_export' => array('integer', (int) $this->enabledCourseExport()),
            'registration_visible' => array('integer', (int) $this->enabledVisibleRegistration()),
            'visible_lua' => array('integer', (int) $this->enabledVisibleLocalUserAdministration()),
            'changeable_lua' => array('integer', (int) $this->enabledChangeableLocalUserAdministration()),
            'group_export' => array('integer', (int) $this->enabledGroupExport()),
            'certificate' => array('integer', (int) $this->enabledCertificate()),
        );

        $ilDB->insert('udf_definition', $values);

        // add table field in usr_defined_data
        $field_id = $next_id;


        $this->__read();

        return $field_id;
    }

    public function delete(int $a_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // Delete definitions
        $query = "DELETE FROM udf_definition " .
            "WHERE field_id = " . $this->db->quote($a_id, 'integer') . " ";
        $ilDB->manipulate($query);

        // Delete usr_data entries
        ilUserDefinedData::deleteEntriesOfField($a_id);

        $this->__read();
    }

    public function update(int $a_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $values = array(
            'field_name' => array('text',$this->getFieldName()),
            'field_type' => array('integer', $this->getFieldType()),
            'field_values' => array('clob',serialize($this->getFieldValues())),
            'visible' => array('integer', (int) $this->enabledVisible()),
            'changeable' => array('integer', (int) $this->enabledChangeable()),
            'required' => array('integer', (int) $this->enabledRequired()),
            'searchable' => array('integer', (int) $this->enabledSearchable()),
            'export' => array('integer', (int) $this->enabledExport()),
            'course_export' => array('integer', (int) $this->enabledCourseExport()),
            'registration_visible' => array('integer', (int) $this->enabledVisibleRegistration()),
            'visible_lua' => array('integer', (int) $this->enabledVisibleLocalUserAdministration()),
            'changeable_lua' => array('integer', (int) $this->enabledChangeableLocalUserAdministration()),
            'group_export' => array('integer', (int) $this->enabledGroupExport()),
            'certificate' => array('integer', (int) $this->enabledCertificate())
        );
        $ilDB->update('udf_definition', $values, array('field_id' => array('integer',$a_id)));
        $this->__read();
    }

    protected function __read(): void
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $query = "SELECT * FROM udf_definition ";
        $res = $this->db->query($query);

        $this->definitions = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->definitions[$row->field_id]['field_id'] = $row->field_id;
            $this->definitions[$row->field_id]['field_name'] = $row->field_name;
            $this->definitions[$row->field_id]['field_type'] = $row->field_type;
            $this->definitions[$row->field_id]['il_id'] = 'il_' . $ilSetting->get('inst_id', '0') . '_udf_' . $row->field_id;

            // #16953
            $tmp = $sort = array();
            $is_numeric = true;
            foreach ((array) unserialize($row->field_values, ['allowed_classes' => false]) as $item) {
                if (!is_numeric($item)) {
                    $is_numeric = false;
                }
                $sort[] = array("value" => $item);
            }
            foreach (ilArrayUtil::sortArray($sort, "value", "asc", $is_numeric) as $item) {
                $tmp[] = $item["value"];
            }

            $this->definitions[$row->field_id]['field_values'] = $tmp;
            $this->definitions[$row->field_id]['visible'] = $row->visible;
            $this->definitions[$row->field_id]['changeable'] = $row->changeable;
            $this->definitions[$row->field_id]['required'] = $row->required;
            $this->definitions[$row->field_id]['searchable'] = $row->searchable;
            $this->definitions[$row->field_id]['export'] = $row->export;
            $this->definitions[$row->field_id]['course_export'] = $row->course_export;
            $this->definitions[$row->field_id]['visib_reg'] = $row->registration_visible;
            $this->definitions[$row->field_id]['visib_lua'] = $row->visible_lua;
            $this->definitions[$row->field_id]['changeable_lua'] = $row->changeable_lua;
            $this->definitions[$row->field_id]['group_export'] = $row->group_export;
            $this->definitions[$row->field_id]['certificate'] = $row->certificate;
        }
    }

    public function deleteValue(
        int $a_field_id,
        int $a_value_id
    ): void {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $old_value = "";

        $definition = $this->getDefinition($a_field_id);

        $counter = 0;
        $new_values = array();
        foreach ($definition['field_values'] as $value) {
            if ($counter++ != $a_value_id) {
                $new_values[] = $value;
            } else {
                $old_value = $value;
            }
        }

        $values = array(
            'field_values' => array('clob',serialize($new_values)));
        $ilDB->update('udf_definition', $values, array('field_id' => array('integer',$a_field_id)));


        // sets value to '' where old value is $old_value
        ilUserDefinedData::deleteFieldValue($a_field_id, $old_value);

        // finally read data
        $this->__read();
    }

    public function toXML(): string
    {
        $xml_writer = new ilXmlWriter();
        $this->addToXML($xml_writer);
        return $xml_writer->xmlDumpMem(false);
    }

    /**
     * add user defined field data to xml (using usr dtd)
     */
    public function addToXML(ilXmlWriter $xml_writer): void
    {
        $xml_writer->xmlStartTag("UDFDefinitions");
        foreach ($this->getDefinitions() as $definition) {
            $attributes = array(
                "Id" => $definition ["il_id"],
                "Type" => $definition["field_type"] == UDF_TYPE_SELECT ? "SELECT" : "TEXT",
                "Visible" => $definition["visible"] ? "TRUE" : "FALSE",
                "Changeable" => $definition["changeable"] ? "TRUE" : "FALSE",
                "Required" => $definition["required"] ? "TRUE" : "FALSE",
                "Searchable" => $definition["searchable"] ? "TRUE" : "FALSE",
                "CourseExport" => $definition["course_export"] ? "TRUE" : "FALSE",
                "GroupExport" => $definition["group_export"] ? "TRUE" : "FALSE",
                "Certificate" => $definition["certificate"] ? "TRUE" : "FALSE",
                "Export" => $definition["export"] ? "TRUE" : "FALSE",
                "RegistrationVisible" => $definition["visib_reg"] ? "TRUE" : "FALSE",
                "LocalUserAdministrationVisible" => $definition["visib_lua"] ? "TRUE" : "FALSE",
                "LocalUserAdministrationChangeable" => $definition["changeable_lua"] ? "TRUE" : "FALSE",

            );
            $xml_writer->xmlStartTag("UDFDefinition", $attributes);
            $xml_writer->xmlElement('UDFName', null, $definition['field_name']);
            if ($definition["field_type"] == UDF_TYPE_SELECT) {
                $field_values = $definition["field_values"];
                foreach ($field_values as $field_value) {
                    $xml_writer->xmlElement('UDFValue', null, $field_value);
                }
            }
            $xml_writer->xmlEndTag("UDFDefinition");
        }
        $xml_writer->xmlEndTag("UDFDefinitions");
    }

    public static function _newInstance(): self
    {
        static $udf = null;
        return $udf = new ilUserDefinedFields();
    }
}
