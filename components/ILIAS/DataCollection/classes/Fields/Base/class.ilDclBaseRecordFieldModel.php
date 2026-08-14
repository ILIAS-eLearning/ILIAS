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

declare(strict_types=1);

class ilDclBaseRecordFieldModel
{
    protected ?int $id = null;
    protected ilDclBaseFieldModel $field;
    protected ilDclBaseRecordModel $record;
    protected ?ilDclBaseRecordRepresentation $record_representation = null;
    protected ?ilDclBaseFieldRepresentation $field_representation = null;
    protected mixed $value = null;
    protected ilObjUser $user;
    protected ilCtrl $ctrl;
    protected ilDBInterface $db;
    protected ilLanguage $lng;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;

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

    public function setUser(ilObjUser $user): void
    {
        $this->user = $user;
    }

    protected function doRead(): void
    {
        if (!$this->getRecord()->getId()) {
            return;
        }

        $query = "SELECT * FROM il_dcl_record_field WHERE field_id = " . $this->db->quote(
            $this->getField()->getId(),
            "integer"
        ) . " AND record_id = "
            . $this->db->quote($this->getRecord()->getId(), "integer");
        $set = $this->db->query($query);
        $rec = $this->db->fetchAssoc($set);
        $this->id = $rec['id'] ?? null;

        $this->loadValue();
    }

    public function doCreate(): void
    {
        $id = $this->db->nextId("il_dcl_record_field");
        $query = "INSERT INTO il_dcl_record_field (id, record_id, field_id) VALUES (" . $this->db->quote(
            $id,
            "integer"
        ) . ", "
            . $this->db->quote(
                $this->getRecord()->getId(),
                "integer"
            ) . ", " . $this->db->quote($this->getField()->getId(), "text") . ")";
        $this->db->manipulate($query);
        $this->id = $id;
    }

    public function doUpdate(): void
    {
        $datatype = $this->getField()->getDatatype();
        $storage_location = ($this->getField()->getStorageLocationOverride() !== null) ? $this->getField()->getStorageLocationOverride() : $datatype->getStorageLocation();

        if ($storage_location != 0) {
            $query = "DELETE FROM il_dcl_stloc" . $storage_location . "_value WHERE record_field_id = "
                . $this->db->quote($this->id, ilDBConstants::T_INTEGER);
            $this->db->manipulate($query);

            $next_id = $this->db->nextId("il_dcl_stloc" . $storage_location . "_value");

            $value = $this->serializeData($this->value);

            if (empty($this->getId())) {
                $this->doCreate();
            }

            $insert_params = [
                'value' => [$this->getDbType($storage_location), $value],
                'record_field_id' => [ilDBConstants::T_INTEGER, $this->getId()],
                'id' => [ilDBConstants::T_INTEGER, $next_id],
            ];

            $this->db->insert("il_dcl_stloc" . $storage_location . "_value", $insert_params);
        }
    }

    private function getDBType(int $storage_location): string
    {
        switch ($storage_location) {
            case 1:
                return ilDBConstants::T_TEXT;
            case 2:
                return ilDBConstants::T_INTEGER;
            case 3:
                return ilDBConstants::T_DATE;
            default:
                throw new InvalidArgumentException('Unsupported storage_location: ' . $storage_location);
        }
    }

    public function delete(): void
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

    public function getValue(): mixed
    {
        $this->loadValue();

        return $this->value;
    }

    public function serializeData(mixed $value): mixed
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return $value;
    }

    public function deserializeData(mixed $value): mixed
    {
        $deserialize = json_decode((string) $value, true);
        if (is_array($deserialize)) {
            return $deserialize;
        }

        return $value;
    }

    public function setValue(mixed $value, bool $omit_parsing = false): void
    {
        $this->loadValue();
        if (!$omit_parsing) {
            $tmp = $this->parseValue($value);
            if ($tmp !== false) {
                $this->value = $tmp;
            }
        } else {
            $this->value = $value;
        }
    }

    public function getFormulaValue(): string
    {
        return (string) $this->getExportValue();
    }

    public function parseExportValue(mixed $value): mixed
    {
        return $value;
    }

    public function getValueFromExcel(ilExcel $excel, int $row, int $col): mixed
    {
        return (string) $excel->getCell($row, $col);
    }

    public function parseValue($value): mixed
    {
        return $value;
    }

    public function getExportValue(): mixed
    {
        return $this->parseExportValue($this->getValue());
    }

    public function fillExcelExport(ilExcel $worksheet, int &$row, int &$col): void
    {
        $worksheet->setCell($row, $col, $this->getExportValue());
        $col++;
    }

    public function getPlainText(): mixed
    {
        return $this->getExportValue();
    }

    public function getSortingValue(bool $link = true): mixed
    {
        return $this->parseSortingValue($this->getValue(), $link);
    }

    public function addHiddenItemsToConfirmation(ilConfirmationGUI $confirmation): void
    {
        if (!is_array($this->getValue())) {
            $confirmation->addHiddenItem('field_' . $this->field->getId(), htmlspecialchars((string) $this->getValue()));
        } else {
            foreach ($this->getValue() as $key => $value) {
                $confirmation->addHiddenItem('field_' . $this->field->getId() . "[$key]", htmlspecialchars((string) $value));
            }
        }
    }

    public function parseSortingValue(mixed $value, bool $link = true): mixed
    {
        return $value;
    }

    protected function loadValue(): void
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

    public function cloneStructure(ilDclBaseRecordFieldModel $old_record_field): void
    {
        $this->setValue($old_record_field->getValue(), true);
        $this->doUpdate();
    }

    public function afterClone(): void
    {
    }

    public function getField(): ilDclBaseFieldModel
    {
        return $this->field;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecord(): ilDclBaseRecordModel
    {
        return $this->record;
    }

    public function getRecordRepresentation(): ?ilDclBaseRecordRepresentation
    {
        return $this->record_representation;
    }

    public function setRecordRepresentation(ilDclBaseRecordRepresentation $record_representation): void
    {
        $this->record_representation = $record_representation;
    }

    public function getFieldRepresentation(): ?ilDclBaseFieldRepresentation
    {
        return $this->field_representation;
    }

    public function setFieldRepresentation(ilDclBaseFieldRepresentation $field_representation): void
    {
        $this->field_representation = $field_representation;
    }
}
