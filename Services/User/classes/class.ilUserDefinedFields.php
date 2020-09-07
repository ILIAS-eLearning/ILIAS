<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

define('UDF_TYPE_TEXT', 1);
define('UDF_TYPE_SELECT', 2);
define('UDF_TYPE_WYSIWYG', 3);
define('UDF_NO_VALUES', 1);
define('UDF_DUPLICATE_VALUES', 2);


/**
* Additional user data fields definition
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
* @ingroup ServicesUser
*/
class ilUserDefinedFields
{
    public $db = null;
    public $definitions = array();

    private $field_visible_registration = 0;

    /**
     * Constructor is private -> use getInstance
     * These definition are used e.g in User XML import.
     * To avoid instances of this class for every user object during import,
     * it caches this object in a singleton.
     *
     */
    private function __construct()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->db = &$ilDB;

        $this->__read();
    }

    /**
     * Get instance
     * @return ilUserDefinedFields
     */
    public static function _getInstance()
    {
        static $udf = null;

        if (!is_object($udf)) {
            return $udf = new ilUserDefinedFields();
        }
        return $udf;
    }

    public function fetchFieldIdFromImportId($a_import_id)
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
        if ($parts[1] != $ilSetting->get('inst_id', 0)) {
            return 0;
        }
        if ($parts[2] != 'udf') {
            return 0;
        }
        if ($parts[3]) {
            // Check if field exists
            if (is_array($this->definitions["$parts[3]"])) {
                return $parts[3];
            }
        }
        return 0;
    }
    public function fetchFieldIdFromName($a_name)
    {
        foreach ($this->definitions as $definition) {
            if ($definition['field_name'] == $a_name) {
                return $definition['field_id'];
            }
        }
        return 0;
    }

    /**
     * @return array
     */
    public function getDefinitions()
    {
        return $this->definitions ? $this->definitions : array();
    }

    public function getDefinition($a_id)
    {
        return is_array($this->definitions[$a_id]) ? $this->definitions[$a_id] : array();
    }

    public function getVisibleDefinitions()
    {
        foreach ($this->definitions as $id => $definition) {
            if ($definition['visible']) {
                $visible_definition[$id] = $definition;
            }
        }
        return $visible_definition ? $visible_definition : array();
    }
    
    public function getLocalUserAdministrationDefinitions()
    {
        foreach ($this->definitions as $id => $definition) {
            if ($definition['visib_lua']) {
                $visible_definition[$id] = $definition;
            }
        }
        return $visible_definition ? $visible_definition : array();
    }
    
    public function getChangeableLocalUserAdministrationDefinitions()
    {
        foreach ($this->definitions as $id => $definition) {
            if ($definition['changeable_lua']) {
                $visible_definition[$id] = $definition;
            }
        }
        return $visible_definition ? $visible_definition : array();
    }

    public function getRegistrationDefinitions()
    {
        foreach ($this->definitions as $id => $definition) {
            if ($definition['visib_reg']) {
                $visible_definition[$id] = $definition;
            }
        }
        return $visible_definition ? $visible_definition : array();
    }

    public function getSearchableDefinitions()
    {
        foreach ($this->definitions as $id => $definition) {
            if ($definition['searchable']) {
                $searchable_definition[$id] = $definition;
            }
        }
        return $searchable_definition ? $searchable_definition : array();
    }
    
    public function getRequiredDefinitions()
    {
        foreach ($this->definitions as $id => $definition) {
            if ($definition['required']) {
                $required_definition[$id] = $definition;
            }
        }
        return $required_definition ? $required_definition : array();
    }

    /**
     * get
     *
     * @access public
     * @param
     *
     */
    public function getCourseExportableFields()
    {
        foreach ($this->definitions as $id => $definition) {
            if ($definition['course_export']) {
                $cexp_definition[$id] = $definition;
            }
        }
        return $cexp_definition ? $cexp_definition : array();
    }

    /**
     * get fields visible in groups
     *
     * @access public
     * @param
     *
     */
    public function getGroupExportableFields()
    {
        foreach ($this->definitions as $id => $definition) {
            if ($definition['group_export']) {
                $cexp_definition[$id] = $definition;
            }
        }
        return $cexp_definition ? $cexp_definition : array();
    }
    
    /**
     * Get exportable field
     * @param int $a_obj_id
     * @return
     */
    public function getExportableFields($a_obj_id)
    {
        if (ilObject::_lookupType($a_obj_id) == 'crs') {
            return $this->getCourseExportableFields();
        }
        if (ilObject::_lookupType($a_obj_id) == 'grp') {
            return $this->getGroupExportableFields();
        }
        return array();
    }
    

    public function setFieldName($a_name)
    {
        $this->field_name = $a_name;
    }
    public function getFieldName()
    {
        return $this->field_name;
    }
    public function setFieldType($a_type)
    {
        $this->field_type = $a_type;
    }
    
    public function isPluginType()
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
    
    public function getFieldType()
    {
        return $this->field_type;
    }
    public function setFieldValues($a_values)
    {
        $this->field_values = array();
        foreach ($a_values as $value) {
            if (strlen($value)) {
                $this->field_values[] = $value;
            }
        }
    }
    public function getFieldValues()
    {
        return $this->field_values ? $this->field_values : array();
    }

    public function enableVisible($a_visible)
    {
        $this->field_visible = $a_visible;
    }
    public function enabledVisible()
    {
        return $this->field_visible;
    }
    public function enableVisibleLocalUserAdministration($a_visible)
    {
        $this->field_visib_lua = $a_visible;
    }
    public function enabledVisibleLocalUserAdministration()
    {
        return $this->field_visib_lua;
    }
    public function enableChangeable($a_changeable)
    {
        $this->field_changeable = $a_changeable;
    }
    public function enabledChangeable()
    {
        return $this->field_changeable;
    }
    public function enableChangeableLocalUserAdministration($a_changeable)
    {
        $this->field_changeable_lua = $a_changeable;
    }
    public function enabledChangeableLocalUserAdministration()
    {
        return $this->field_changeable_lua;
    }
    public function enableRequired($a_required)
    {
        $this->field_required = $a_required;
    }
    public function enabledRequired()
    {
        return $this->field_required;
    }
    public function enableSearchable($a_searchable)
    {
        $this->field_searchable = $a_searchable;
    }
    public function enabledSearchable()
    {
        return $this->field_searchable;
    }
    public function enableExport($a_export)
    {
        $this->field_export = $a_export;
    }
    public function enabledExport()
    {
        return $this->field_export;
    }
    public function enableCourseExport($a_course_export)
    {
        $this->field_course_export = $a_course_export;
    }
    public function enabledCourseExport()
    {
        return $this->field_course_export;
    }
    public function enableGroupExport($a_group_export)
    {
        $this->field_group_export = $a_group_export;
    }
    public function enabledGroupExport()
    {
        return $this->field_group_export;
    }

    public function enableCertificate($a_c)
    {
        $this->field_certificate = $a_c;
    }
    public function enabledCertificate()
    {
        return $this->field_certificate;
    }

    public function enableVisibleRegistration($a_visible_registration)
    {
        $this->field_visible_registration = $a_visible_registration;
    }
    public function enabledVisibleRegistration()
    {
        return $this->field_visible_registration;
    }

    /**
     * @param mixed[] $a_values
     * @param bool $a_with
     * @return array
     */
    public function fieldValuesToSelectArray($a_values, $a_with_selection_info = true)
    {
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

    public function validateValues()
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

    public function nameExists($a_field_name)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM udf_definition " .
            "WHERE field_name = " . $this->db->quote($a_field_name, 'text') . " ";
        $res = $ilDB->query($query);

        return (bool) $res->numRows();
    }

    public function add()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // Add definition entry
        $next_id = $ilDB->nextId('udf_definition');
        
        $values = array(
            'field_id' => array('integer',$next_id),
            'field_name' => array('text',$this->getFieldName()),
            'field_type' => array('integer', (int) $this->getFieldType()),
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
    public function delete($a_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // Delete definitions
        $query = "DELETE FROM udf_definition " .
            "WHERE field_id = " . $this->db->quote($a_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        // Delete usr_data entries
        //		$ilDB->dropTableColumn('udf_data','f_'.$a_id);
        include_once("./Services/User/classes/class.ilUserDefinedData.php");
        ilUserDefinedData::deleteEntriesOfField($a_id);

        $this->__read();

        return true;
    }

    public function update($a_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $values = array(
            'field_name' => array('text',$this->getFieldName()),
            'field_type' => array('integer', (int) $this->getFieldType()),
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

        return true;
    }



    // Private
    public function __read()
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
            $this->definitions[$row->field_id]['il_id'] = 'il_' . $ilSetting->get('inst_id', 0) . '_udf_' . $row->field_id;

            // #16953
            $tmp = $sort = array();
            $is_numeric = true;
            foreach ((array) unserialize($row->field_values) as $item) {
                if (!is_numeric($item)) {
                    $is_numeric = false;
                }
                $sort[] = array("value" => $item);
            }
            foreach (ilUtil::sortArray($sort, "value", "asc", $is_numeric) as $item) {
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
            // fraunhpatch start
            $this->definitions[$row->field_id]['certificate'] = $row->certificate;
            // fraunhpatch end
        }

        return true;
    }
    

    public function deleteValue($a_field_id, $a_value_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
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
        include_once("./Services/User/classes/class.ilUserDefinedData.php");
        ilUserDefinedData::deleteFieldValue($a_field_id, $old_value);

        // fianally read data
        $this->__read();

        return true;
    }

    public function toXML()
    {
        include_once './Services/Xml/classes/class.ilXmlWriter.php';
        $xml_writer = new ilXmlWriter();

        $this->addToXML($xml_writer);

        return $xml_writer->xmlDumpMem(false);
    }

    /**
    *	add user defined field data to xml (using usr dtd)
    *	@param*XmlWriter $xml_writer
    */
    public function addToXML($xml_writer)
    {
        $xml_writer->xmlStartTag("UDFDefinitions");
        foreach ($this->getDefinitions() as $definition) {
            $attributes = array(
                "Id" => $definition ["il_id"],
                "Type" => $definition["field_type"] == UDF_TYPE_SELECT? "SELECT" : "TEXT",
                "Visible" => $definition["visible"]? "TRUE" : "FALSE",
                "Changeable" => $definition["changeable"]? "TRUE" : "FALSE",
                "Required" => $definition["required"]? "TRUE" : "FALSE",
                "Searchable" => $definition["searchable"]? "TRUE" : "FALSE",
                "CourseExport" => $definition["course_export"]? "TRUE" : "FALSE",
                "GroupExport" => $definition["group_export"]? "TRUE" : "FALSE",
                "Certificate" => $definition["certificate"]? "TRUE" : "FALSE",
                "Export" => $definition["export"]? "TRUE" : "FALSE",
                "RegistrationVisible" => $definition["visib_reg"]? "TRUE" : "FALSE",
                "LocalUserAdministrationVisible" => $definition["visib_lua"]? "TRUE" : "FALSE",
                "LocalUserAdministrationChangeable" => $definition["changeable_lua"]? "TRUE" : "FALSE",
                
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


    public static function _newInstance()
    {
        static $udf = null;

        return $udf = new ilUserDefinedFields();
    }
}
