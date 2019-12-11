<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/DataCollection/exceptions/class.ilDclInputException.php';

/**
 * Class ilDclBaseFieldModel
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDclBaseRecordFieldModel
{

    /**
     * @var int
     */
    protected $id;
    /**
     * @var ilDclBaseFieldModel
     */
    protected $field;
    /**
     * @var ilDclBaseRecordModel
     */
    protected $record;
    /**
     * @var ilDclBaseRecordRepresentation
     */
    protected $record_representation;
    /**
     * @var ilDclBaseFieldRepresentation
     */
    protected $field_representation;
    /**
     * @var string
     */
    protected $value;
    /**
     * @var ilObjUser
     */
    protected $user;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilDB
     */
    protected $db;
    /**
     * @var ilLanguage
     */
    protected $lng;


    /**
     * @param ilDclBaseRecordModel $record
     * @param ilDclBaseFieldModel  $field
     */
    public function __construct(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $this->record = $record;
        $this->field = $field;
        $this->ctrl = $ilCtrl;
        $this->user = $ilUser;
        $this->db = $ilDB;
        $this->lng = $lng;
        $this->doRead();
    }


    /**
     * Read object data from database
     */
    protected function doRead()
    {
        if (!$this->getRecord()->getId()) {
            return;
        }

        $query = "SELECT * FROM il_dcl_record_field WHERE field_id = " . $this->db->quote($this->getField()->getId(), "integer") . " AND record_id = "
            . $this->db->quote($this->getRecord()->getId(), "integer");
        $set = $this->db->query($query);
        $rec = $this->db->fetchAssoc($set);
        $this->id = $rec['id'];

        $this->loadValue();
    }


    /**
     * Creates an Id and a database entry.
     */
    public function doCreate()
    {
        $id = $this->db->nextId("il_dcl_record_field");
        $query = "INSERT INTO il_dcl_record_field (id, record_id, field_id) VALUES (" . $this->db->quote($id, "integer") . ", "
            . $this->db->quote($this->getRecord()->getId(), "integer") . ", " . $this->db->quote($this->getField()->getId(), "text") . ")";
        $this->db->manipulate($query);
        $this->id = $id;
    }


    /**
     * Update object in database
     */
    public function doUpdate()
    {
        //$this->loadValue(); //Removed Mantis #0011799
        $datatype = $this->getField()->getDatatype();
        $storage_location = ($this->getField()->getStorageLocationOverride() !== null) ? $this->getField()->getStorageLocationOverride() : $datatype->getStorageLocation();

        if ($storage_location != 0) {
            $query = "DELETE FROM il_dcl_stloc" . $storage_location . "_value WHERE record_field_id = "
                . $this->db->quote($this->id, "integer");
            $this->db->manipulate($query);

            $next_id = $this->db->nextId("il_dcl_stloc" . $storage_location . "_value");

            // This is a workaround to ensure that date values in stloc3 are never stored as NULL, which is not allowed
            if ($storage_location == 3 && (is_null($this->value) || empty($this->value))) {
                $this->value = '0000-00-00 00:00:00';
            }

            $value = $this->serializeData($this->value);

            if ($this->getId() == 0) {
                $this->doCreate();
            }

            $insert_params = array(
                "value"           => array($datatype->getDbType(), $value),
                "record_field_id" => array("integer", $this->getId()),
                "id"              => array("integer", $next_id),
            );

            $this->db->insert("il_dcl_stloc" . $storage_location . "_value", $insert_params);
        }
    }


    /**
     * Delete record field in database
     */
    public function delete()
    {
        $datatype = $this->getField()->getDatatype();
        $storage_location = ($this->getField()->getStorageLocationOverride() !== null) ? $this->getField()->getStorageLocationOverride() : $datatype->getStorageLocation();

        if ($storage_location != 0) {
            $query = "DELETE FROM il_dcl_stloc" . $storage_location . "_value WHERE record_field_id = "
                . $this->db->quote($this->id, "integer");
            $this->db->manipulate($query);
        }

        $query2 = "DELETE FROM il_dcl_record_field WHERE id = " . $this->db->quote($this->id, "integer");
        $this->db->manipulate($query2);
    }


    /**
     * @return string|array
     */
    public function getValue()
    {
        $this->loadValue();

        return $this->value;
    }


    /**
     * @return array|string
     */
    public function getValueForRepresentation()
    {
        return $this->getValue();
    }


    /**
     * Serialize data before storing to db
     *
     * @param $value mixed
     *
     * @return mixed
     */
    public function serializeData($value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return $value;
    }


    /**
     * Deserialize data before applying to field
     *
     * @param $value mixed
     *
     * @return mixed
     */
    public function deserializeData($value)
    {
        $deserialize = json_decode($value, true);
        if (is_array($deserialize)) {
            return $deserialize;
        }

        return $value;
    }


    /**
     * Set value for record field
     *
     * @param mixed $value
     * @param bool  $omit_parsing If true, does not parse the value and stores it in the given format
     */
    public function setValue($value, $omit_parsing = false)
    {
        $this->loadValue();
        if (!$omit_parsing) {
            $tmp = $this->parseValue($value, $this);
            $old = $this->value;
            //if parse value fails keep the old value
            if ($tmp !== false) {
                $this->value = $tmp;
            }
        } else {
            $this->value = $value;
        }
    }


    /**
     * @param $form ilPropertyFormGUI
     */
    public function setValueFromForm($form)
    {
        $value = $form->getInput("field_" . $this->getField()->getId());

        $this->setValue($value);
    }


    /**
     * Function to parse incoming data from form input value $value. returns the string/number/etc. to store in the database.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function parseExportValue($value)
    {
        return $value;
    }


    /**
     * @param $excel
     * @param $row
     * @param $col
     *
     * @return array|string
     */
    public function getValueFromExcel($excel, $row, $col)
    {
        $value = $excel->getCell($row, $col);

        return $value;
    }


    /**
     * Function to parse incoming data from form input value $value. returns the string/number/etc. to store in the database.
     *
     * @param $value
     *
     * @return int|string
     */
    public function parseValue($value)
    {
        return $value;
    }


    /**
     * @return int|string
     */
    public function getExportValue()
    {
        return $this->parseExportValue($this->getValue());
    }


    /**
     * @param $worksheet
     * @param $row
     * @param $col
     */
    public function fillExcelExport(ilExcel $worksheet, &$row, &$col)
    {
        $worksheet->setCell($row, $col, $this->getExportValue());
        $col++;
    }


    /**
     * @return mixed used for the sorting.
     */
    public function getPlainText()
    {
        return $this->getExportValue();
    }


    public function getSortingValue($link = true)
    {
        return $this->parseSortingValue($this->getValue(), $this, $link);
    }


    /**
     * @param ilConfirmationGUI $confirmation
     */
    public function addHiddenItemsToConfirmation(ilConfirmationGUI &$confirmation)
    {
        ;
        if (!is_array($this->getValue())) {
            $confirmation->addHiddenItem('field_' . $this->field->getId(), $this->getValue());
        } else {
            foreach ($this->getValue() as $key => $value) {
                $confirmation->addHiddenItem('field_' . $this->field->getId() . "[$key]", $value);
            }
        }
    }


    /**
     * Returns sortable value for the specific field-types
     *
     * @param                           $value
     * @param ilDclBaseRecordFieldModel $record_field
     * @param bool|true                 $link
     *
     * @return int|string
     */
    public function parseSortingValue($value, $link = true)
    {
        return $value;
    }


    /**
     * Load the value
     */
    protected function loadValue()
    {
        if ($this->value === null) {
            $datatype = $this->getField()->getDatatype();

            $storage_location = ($this->getField()->getStorageLocationOverride() !== null) ? $this->getField()->getStorageLocationOverride() : $datatype->getStorageLocation();
            if ($storage_location != 0) {
                $query = "SELECT * FROM il_dcl_stloc" . $storage_location . "_value WHERE record_field_id = "
                    . $this->db->quote($this->id, "integer");

                $set = $this->db->query($query);
                $rec = $this->db->fetchAssoc($set);
                $value = $this->deserializeData($rec['value']);
                $this->value = $value;
            }
        }
    }


    /**
     * @param ilDclBaseRecordFieldModel $old_record_field
     */
    public function cloneStructure(ilDclBaseRecordFieldModel $old_record_field)
    {
        $this->setValue($old_record_field->getValue());
        $this->doUpdate();
    }


    /**
     *
     */
    public function afterClone()
    {
    }


    /**
     * @return ilDclBaseFieldModel
     */
    public function getField()
    {
        return $this->field;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return ilDclBaseRecordModel
     */
    public function getRecord()
    {
        return $this->record;
    }


    /**
     * @return ilDclBaseRecordRepresentation
     */
    public function getRecordRepresentation()
    {
        return $this->record_representation;
    }


    /**
     * @param ilDclBaseRecordRepresentation $record_representation
     */
    public function setRecordRepresentation($record_representation)
    {
        $this->record_representation = $record_representation;
    }


    /**
     * @return ilDclBaseFieldRepresentation
     */
    public function getFieldRepresentation()
    {
        return $this->field_representation;
    }


    /**
     * @param ilDclBaseFieldRepresentation $field_representation
     */
    public function setFieldRepresentation($field_representation)
    {
        $this->field_representation = $field_representation;
    }
}
