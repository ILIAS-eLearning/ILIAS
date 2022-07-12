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
 ********************************************************************
 */

/**
 * Class ilDclBaseFieldModel
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 * @ingroup ModulesDataCollection
 */
class ilDclBaseRecordFieldModel
{
    protected int $id = 0;
    protected ilDclBaseFieldModel $field;
    protected ilDclBaseRecordModel $record;
    protected ?ilDclBaseRecordRepresentation $record_representation = null;
    protected ?ilDclBaseFieldRepresentation $field_representation = null;
    /** @var int|float|array|null */
    protected $value;
    protected ilObjUser $user;
    protected ilCtrl $ctrl;
    protected ilDBInterface $db;
    protected ilLanguage $lng;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;

    /**
     * @param ilDclBaseRecordModel $record
     * @param ilDclBaseFieldModel  $field
     */
    public function __construct(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field)
    {
        global $DIC;

        $this->record = $record;
        $this->field = $field;
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->doRead();
    }

    /**
     * Read object data from database
     */
    protected function doRead() : void
    {
        if (!$this->getRecord()->getId()) {
            return;
        }

        $query = "SELECT * FROM il_dcl_record_field WHERE field_id = " . $this->db->quote($this->getField()->getId(),
                "integer") . " AND record_id = "
            . $this->db->quote($this->getRecord()->getId(), "integer");
        $set = $this->db->query($query);
        $rec = $this->db->fetchAssoc($set);
        $this->id = $rec['id'] ?? null;

        $this->loadValue();
    }

    /**
     * Creates an Id and a database entry.
     */
    public function doCreate() : void
    {
        $id = $this->db->nextId("il_dcl_record_field");
        $query = "INSERT INTO il_dcl_record_field (id, record_id, field_id) VALUES (" . $this->db->quote($id,
                "integer") . ", "
            . $this->db->quote($this->getRecord()->getId(),
                "integer") . ", " . $this->db->quote($this->getField()->getId(), "text") . ")";
        $this->db->manipulate($query);
        $this->id = $id;
    }

    /**
     * Update object in database
     */
    public function doUpdate() : void
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
                "value" => array($datatype->getDbType(), $value),
                "record_field_id" => array("integer", $this->getId()),
                "id" => array("integer", $next_id),
            );

            $this->db->insert("il_dcl_stloc" . $storage_location . "_value", $insert_params);
        }
    }

    /**
     * Delete record field in database
     */
    public function delete() : void
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
     * @param mixed $value
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
     * @param mixed $value
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
     * @param mixed $value
     * @param bool  $omit_parsing If true, does not parse the value and stores it in the given format
     */
    public function setValue($value, bool $omit_parsing = false) : void
    {
        $this->loadValue();
        if (!$omit_parsing) {
            $tmp = $this->parseValue($value);
            $old = $this->value;
            //if parse value fails keep the old value
            if ($tmp !== false) {
                $this->value = $tmp;
            }
        } else {
            $this->value = $value;
        }
    }

    public function setValueFromForm(ilPropertyFormGUI $form) : void
    {
        $value = $form->getInput("field_" . $this->getField()->getId());

        $this->setValue($value);
    }

    public function getFormulaValue() : string
    {
        return $this->getExportValue();
    }

    /**
     * Function to parse incoming data from form input value $value. returns the string/number/etc. to store in the database.
     * @param mixed $value
     * @return mixed
     */
    public function parseExportValue($value)
    {
        return $value;
    }

    /**
     * @return int|string
     */
    public function getValueFromExcel(ilExcel $excel, int $row, int $col)
    {
        $value = $excel->getCell($row, $col);
        return $value;
    }

    /**
     * Function to parse incoming data from form input value $value. returns the string/number/etc. to store in the database.
     * @param int|string $value
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

    public function fillExcelExport(ilExcel $worksheet, int &$row, int &$col) : void
    {
        $worksheet->setCell($row, $col, $this->getExportValue());
        $col++;
    }

    /**
     * @return int|string
     */
    public function getPlainText()
    {
        return $this->getExportValue();
    }

    /**
     * @param bool $link
     * @return int|string
     */
    public function getSortingValue(bool $link = true)
    {
        return $this->parseSortingValue($this->getValue(), $link);
    }

    /**
     * @param ilConfirmationGUI $confirmation
     */
    public function addHiddenItemsToConfirmation(ilConfirmationGUI $confirmation)
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
     * @param int|string $value
     * @return int|string
     */
    public function parseSortingValue($value, bool $link = true)
    {
        return $value;
    }

    /**
     * Load the value
     */
    protected function loadValue() : void
    {
        if ($this->value === null) {
            $datatype = $this->getField()->getDatatype();

            $storage_location = ($this->getField()->getStorageLocationOverride() !== null) ? $this->getField()->getStorageLocationOverride() : $datatype->getStorageLocation();
            if ($storage_location != 0) {
                $query = "SELECT * FROM il_dcl_stloc" . $storage_location . "_value WHERE record_field_id = "
                    . $this->db->quote($this->id, "integer");

                $set = $this->db->query($query);
                $rec = $this->db->fetchAssoc($set);
                $value = $this->deserializeData($rec['value'] ?? null);
                $this->value = $value;
            }
        }
    }

    /**
     * @param ilDclBaseRecordFieldModel $old_record_field
     */
    public function cloneStructure(ilDclBaseRecordFieldModel $old_record_field) : void
    {
        $this->setValue($old_record_field->getValue());
        $this->doUpdate();
    }

    /**
     *
     */
    public function afterClone() : void
    {
    }

    public function getField() : ilDclBaseFieldModel
    {
        return $this->field;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getRecord() : ilDclBaseRecordModel
    {
        return $this->record;
    }

    public function getRecordRepresentation() : ?ilDclBaseRecordRepresentation
    {
        return $this->record_representation;
    }

    public function setRecordRepresentation(ilDclBaseRecordRepresentation $record_representation) : void
    {
        $this->record_representation = $record_representation;
    }

    public function getFieldRepresentation() : ?ilDclBaseFieldRepresentation
    {
        return $this->field_representation;
    }

    public function setFieldRepresentation(ilDclBaseFieldRepresentation $field_representation) : void
    {
        $this->field_representation = $field_representation;
    }
}
