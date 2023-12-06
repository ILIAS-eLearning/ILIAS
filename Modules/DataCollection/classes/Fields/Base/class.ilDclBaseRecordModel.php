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

use ILIAS\Notes\Service;

/**
 * Class ilDclBaseRecordModel
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 * @ingroup ModulesDataCollection
 */
class ilDclBaseRecordModel
{
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\UI\Renderer $renderer;
    protected Service $notes;

    /**
     * @var ilDclBaseRecordFieldModel[]
     */
    protected ?array $recordfields = null;
    protected int $id = 0;
    protected int $table_id;
    protected ?ilDclTable $table = null;
    protected ?int $last_edit_by = null;
    protected int $owner = 0;
    protected ilDateTime $last_update;
    protected ilDateTime $create_date;
    protected ?int $nr_of_comments = null;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    protected ilDBInterface $db;
    protected ilAppEventHandler $event;
    private ilObjUser $user;

    public function __construct(?int $a_id = 0)
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->event = $DIC->event();
        $this->user = $DIC->user();
        $this->ui_factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();

        if ($a_id && $a_id != 0) {
            $this->id = $a_id;
            $this->doRead();
        }

        $this->notes = $DIC->notes();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    public function doUpdate(bool $omit_notification = false): void
    {
        $values = [
            "table_id" => [
                "integer",
                $this->getTableId(),
            ],
            "last_update" => [
                "date",
                $this->getLastUpdate()->get(IL_CAL_DATETIME),
            ],
            "owner" => [
                "integer",
                $this->getOwner(),
            ],
            "last_edit_by" => [
                "integer",
                $this->getLastEditBy(),
            ],
        ];
        $this->db->update(
            "il_dcl_record",
            $values,
            [
                "id" => [
                    "integer",
                    $this->id,
                ],
            ]
        );

        foreach ($this->getRecordFields() as $recordfield) {
            $recordfield->doUpdate();
        }

        //TODO: add event raise
        if (!$omit_notification) {
            $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
            $objDataCollection = new ilObjDataCollection($ref_id);
            $objDataCollection->sendNotification("update_record", $this->getTableId(), $this->id);
        }
    }

    public function doRead(): void
    {
        //build query
        $query = "Select * From il_dcl_record WHERE id = " . $this->db->quote($this->getId(), "integer") . " ORDER BY id";

        $set = $this->db->query($query);
        $rec = $this->db->fetchAssoc($set);

        if (!$rec) {
            $this->id = 0;
            return;
        }

        $this->setTableId((int) $rec["table_id"]);
        if (null !== $rec["create_date"]) {
            $this->setCreateDate(new ilDateTime($rec["create_date"], IL_CAL_DATETIME));
        }
        if (null !== $rec["last_update"]) {
            $this->setLastUpdate(new ilDateTime($rec["last_update"], IL_CAL_DATETIME));
        } else {
            $this->setLastUpdate(new ilDateTime($rec["create_date"], IL_CAL_DATETIME));
        }
        $this->setOwner((int) $rec["owner"]);
        if (null !== $rec["last_edit_by"]) {
            $this->setLastEditBy((int) $rec["last_edit_by"]);
        }
    }

    /**
     * @throws ilException
     */
    public function doCreate(): void
    {
        if (!ilDclTable::_tableExists($this->getTableId())) {
            throw new ilException("The field does not have a related table!");
        }

        $id = $this->db->nextId("il_dcl_record");
        $this->setId($id);
        $query
            = "INSERT INTO il_dcl_record (
			id,
			table_id,
			create_date,
			Last_update,
			owner,
			last_edit_by
			) VALUES (" . $this->db->quote($this->getId(), "integer") . "," . $this->db->quote(
                $this->getTableId(),
                "integer"
            ) . ","
            . $this->db->quote($this->getCreateDate()->get(IL_CAL_DATETIME), "timestamp") . "," . $this->db->quote(
                $this->getLastUpdate()->get(IL_CAL_DATETIME),
                "timestamp"
            ) . ","
            . $this->db->quote($this->getOwner(), "integer") . "," . $this->db->quote($this->getLastEditBy(), "integer") . "
			)";
        $this->db->manipulate($query);

        $this->loadRecordFields();
        foreach ($this->getRecordFields() as $recordField) {
            $recordField->doCreate();
        }

        $this->getTable()->loadRecords();
    }

    public function deleteField(int $field_id): void
    {
        $this->loadRecordFields();
        $this->recordfields[$field_id]->delete();
        if (count($this->recordfields) == 1) {
            $this->doDelete();
        }
    }

    public function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTableId(int $a_id): void
    {
        $this->table_id = $a_id;
    }

    public function getTableId(): int
    {
        return $this->table_id;
    }

    public function setCreateDate(ilDateTime $a_datetime): void
    {
        $this->create_date = $a_datetime;
    }

    public function getCreateDate(): ilDateTime
    {
        return $this->create_date;
    }

    public function setLastUpdate(ilDateTime $a_datetime): void
    {
        $this->last_update = $a_datetime;
    }

    public function getLastUpdate(): ilDateTime
    {
        return $this->last_update;
    }

    public function setOwner(int $a_id): void
    {
        $this->owner = $a_id;
    }

    public function getOwner(): int
    {
        return $this->owner;
    }

    public function getLastEditBy(): ?int
    {
        return $this->last_edit_by;
    }

    public function setLastEditBy(?int $last_edit_by): void
    {
        $this->last_edit_by = $last_edit_by;
    }

    /**
     * @param int|string $field_id
     * @param int|string $value
     * @return void
     */
    public function setRecordFieldValue($field_id, $value): void
    {
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            $this->setStandardField($field_id, $value);
        } else {
            $this->loadTable();
            $this->recordfields[$field_id]->setValue($value);
        }
    }

    /**
     * Set a field value
     * @param int|string $field_id
     */
    public function setRecordFieldValueFromForm(int $field_id, ilPropertyFormGUI $form): void
    {
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            $this->setStandardFieldFromForm($field_id, $form);
        } else {
            $this->loadTable();
            $this->recordfields[$field_id]->setValueFromForm($form);
        }
    }

    /**
     * @return int|string
     */
    public function getRecordFieldValueFromExcel(ilExcel $excel, int $row, int $col, ilDclBaseFieldModel $field)
    {
        $this->loadRecordFields();

        return $this->recordfields[$field->getId()]->getValueFromExcel($excel, $row, $col);
    }

    public function setStandardFieldValueFromExcel(
        ilExcel $excel,
        int $row,
        int $col,
        ilDclBaseFieldModel $field
    ): void {
        $value = $field->getValueFromExcel($excel, $row, $col);
        if ($value) {
            $this->{$field->getId()} = $value;
        }
    }

    public function getRecordFieldValues(): array
    {
        $this->loadRecordFields();
        $return = [];
        foreach ($this->recordfields as $id => $record_field) {
            $return[$id] = $record_field->getValue();
        }

        return $return;
    }

    /**
     * Get Field Value
     * @return int|string|array|null
     */
    public function getRecordFieldValue(?int $field_id)
    {
        if ($field_id === null) {
            return null;
        }
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            return $this->getStandardField($field_id);
        } else {
            return $this->recordfields[$field_id]->getValue();
        }
    }

    /**
     * Get Field Value for Representation in a Form
     * @param ?int|string $field_id
     * @return array|int|null|string
     */
    public function getRecordFieldRepresentationValue($field_id)
    {
        if ($field_id === null) {
            return null;
        }
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            return $this->getStandardField($field_id);
        } else {
            return $this->recordfields[$field_id]->getValueForRepresentation();
        }
    }

    /**
     * Get Field Export Value
     * @param ?int|string $field_id
     * @return int|string
     */
    public function getRecordFieldExportValue($field_id)
    {
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            return $this->getStandardFieldHTML($field_id);
        } else {
            return $this->recordfields[$field_id]->getExportValue();
        }
    }

    /**
     * Get Field Export Value
     * @param int|string $field_id
     * @return int|string
     */
    public function getRecordFieldPlainText($field_id)
    {
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            return $this->getStandardFieldHTML($field_id);
        } else {
            return $this->recordfields[$field_id]->getPlainText();
        }
    }

    /**
     * @param int|string $field_id
     */
    public function fillRecordFieldExcelExport(ilExcel $worksheet, int &$row, int &$col, $field_id): void
    {
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            if ($field_id == 'owner') {
                $worksheet->setCell($row, $col, ilObjUser::_lookupLogin($this->getOwner()));
                $col++;
                $name_array = ilObjUser::_lookupName($this->getOwner());
                $worksheet->setCell($row, $col, $name_array['lastname'] . ', ' . $name_array['firstname']);
            } elseif ('last_update') {
                $date_time = $this->getLastUpdate()->get(IL_CAL_DATETIME, '', $this->user->getTimeZone());
                $worksheet->setCell($row, $col, $date_time);
            } elseif ('create_date') {
                $date_time = $this->getCreateDate()->get(IL_CAL_DATETIME, '', $this->user->getTimeZone());
                $worksheet->setCell($row, $col, $date_time);
            } else {
                $worksheet->setCell($row, $col, $this->getStandardFieldHTML($field_id));
            }
            $col++;
        } else {
            $this->recordfields[$field_id]->fillExcelExport($worksheet, $row, $col);
        }
    }

    /**
     * @param int|string $field_id
     */
    public function getRecordFieldFormulaValue($field_id): string
    {
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            $value = $this->getStandardFieldFormulaValue($field_id);
        } else {
            if (is_object($this->recordfields[$field_id])) {
                $value = $this->recordfields[$field_id]->getFormulaValue();
            } else {
                $value = '';
            }
        }

        return $value;
    }

    /**
     * @param int|string $field_id
     */
    public function getRecordFieldHTML($field_id, array $options = []): string
    {
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            $html = $this->getStandardFieldHTML($field_id, $options);
        } else {
            if (array_key_exists($field_id, $this->recordfields) && is_object($this->recordfields[$field_id])) {
                $html = $this->recordfields[$field_id]->getRecordRepresentation()->getHTML(true, $options);
            } else {
                $html = '';
            }
        }

        return $html;
    }

    /**
     * @param int|string $field_id
     */
    public function getRecordFieldSortingValue($field_id, array $options = []): string
    {
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            $html = $this->getStandardFieldHTML($field_id, $options);
        } else {
            if (is_object($this->recordfields[$field_id])) {
                $html = $this->recordfields[$field_id]->getSortingValue();
            } else {
                $html = '';
            }
        }

        return $html;
    }

    /**
     * @param int|string $field_id
     */
    public function getRecordFieldSingleHTML($field_id, array $options = []): string
    {
        $this->loadRecordFields();

        if (ilDclStandardField::_isStandardField($field_id)) {
            $html = $this->getStandardFieldHTML($field_id);
        } else {
            $field = $this->recordfields[$field_id];
            /**
             * @var $field ilDclBaseRecordFieldModel
             */

            $html = $field->getRecordRepresentation()->getSingleHTML($options, false);
        }

        return $html;
    }

    /**
     * @param int|string $field_id
     */
    public function fillRecordFieldFormInput($field_id, ilPropertyFormGUI $form): void
    {
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            $this->fillStandardFieldFormInput($field_id, $form);
        } else {
            $this->recordfields[$field_id]->getRecordRepresentation()->fillFormInput($form);
        }
    }

    /**
     * @param int|string $field_id
     */
    protected function setStandardFieldFromForm($field_id, ilPropertyFormGUI $form): void
    {
        if ($item = $form->getItemByPostVar("field_" . $field_id)) {
            $this->setStandardField($field_id, $item->getValue());
        }
    }

    /**
     * @param int|string $field_id
     * @param int|string $value
     */
    protected function setStandardField($field_id, $value)
    {
        if ($field_id == "last_edit_by") {
            $this->setLastEditBy($value);
            return;
        }
        $this->{$field_id} = $value;
    }

    /**
     * @param int|string $field_id
     */
    protected function fillStandardFieldFormInput($field_id, ilPropertyFormGUI $form): void
    {
        if ($item = $form->getItemByPostVar('field_' . $field_id)) {
            $item->setValue($this->getStandardField($field_id));
        }
    }

    /**
     * @param int|string $field_id
     */
    protected function getStandardField($field_id): string
    {
        switch ($field_id) {
            case "last_edit_by":
                return $this->getLastEditBy();
            case 'owner':
                $usr_data = ilObjUser::_lookupName($this->getOwner());
                return $usr_data['login'];
        }

        return $this->{$field_id};
    }

    /**
     * @param int|string $field_id
     */
    public function getStandardFieldFormulaValue($field_id): string
    {
        return $this->getStandardFieldHTML($field_id);
    }

    public function getStandardFieldHTML(string $field_id, array $options = []): string
    {
        switch ($field_id) {
            case 'id':
                return $this->getId();
            case 'owner':
                return ilUserUtil::getNamePresentation($this->getOwner());
            case 'last_edit_by':
                return ilUserUtil::getNamePresentation($this->getLastEditBy());
            case 'last_update':
                return ilDatePresentation::formatDate($this->getLastUpdate());
            case 'create_date':
                return ilDatePresentation::formatDate($this->getCreateDate());
            case 'comments':

                $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());

                $ajax_hash = ilCommonActionDispatcherGUI::buildAjaxHash(
                    1,
                    $ref_id,
                    'dcl',
                    $this->table->getCollectionObject()
                                ->getId(),
                    'dcl',
                    $this->getId()
                );
                $update_code = "il.UI.counter.getCounterObject($(\".ilc_page_Page\")).incrementStatusCount(1);";
                $ajax_link = ilNoteGUI::getListCommentsJSCall($ajax_hash, $update_code);

                $nr_comments = $this->getNrOfComments();

                $comment_glyph = $this->ui_factory->symbol()->glyph()->comment()->withCounter(
                    $this->ui_factory->counter()->status($nr_comments)
                )->withAdditionalOnLoadCode(function ($id) use ($ajax_link): string {
                    return "document.getElementById('$id').onclick = function (event) { $ajax_link; };";
                });
                return $this->renderer->render($comment_glyph);
        }

        return "";
    }

    /**
     * @param string $field_id
     * @return int|string
     */
    public function getStandardFieldPlainText(string $field_id)
    {
        switch ($field_id) {
            case 'comments':
                return $this->getNrOfComments();
            default:
                return strip_tags($this->getStandardFieldHTML($field_id));
        }
    }

    private function loadRecordFields(): void
    {
        if ($this->recordfields == null) {
            $this->loadTable();
            $recordfields = [];
            foreach ($this->table->getRecordFields() as $field) {
                if (($recordfields[$field->getId()] ?? null) === null) {
                    $recordfields[$field->getId()] = ilDclCache::getRecordFieldCache($this, $field);
                }
            }

            $this->recordfields = $recordfields;
        }
    }

    private function loadTable(): void
    {
        if ($this->table === null) {
            $this->table = ilDclCache::getTableCache($this->getTableId());
        }
    }

    public function getRecordField(int $field_id): ilDclBaseRecordFieldModel
    {
        $this->loadRecordFields();

        return $this->recordfields[$field_id];
    }

    public function doDelete(bool $omit_notification = false): void
    {
        $this->loadRecordFields();
        foreach ($this->recordfields as $recordfield) {
            if ($recordfield->getField()->getDatatypeId() == ilDclDatatype::INPUTFORMAT_FILE) {
                $this->deleteFile((int)$recordfield->getValue());
            }

            if ($recordfield->getField()->getDatatypeId() == ilDclDatatype::INPUTFORMAT_MOB) {
                $this->deleteMob((int)$recordfield->getValue());
            }

            $recordfield->delete();
        }

        $query = "DELETE FROM il_dcl_record WHERE id = " . $this->db->quote($this->getId(), "integer");
        $this->db->manipulate($query);

        $this->table->loadRecords();

        if (!$omit_notification) {
            $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());
            $objDataCollection = new ilObjDataCollection($ref_id);
            $objDataCollection->sendNotification("delete_record", $this->getTableId(), $this->getId());

            $this->event->raise(
                'Modules/DataCollection',
                'deleteRecord',
                [
                    'dcl' => ilDclCache::getTableCache($this->getTableId())->getCollectionObject(),
                    'table_id' => $this->table_id,
                    'record_id' => $this->getId(),
                    'record' => $this,
                ]
            );
        }
    }

    // TODO: Find better way to copy data (including all references)
    public function cloneStructure(int $original_id, array $new_fields): void
    {
        $original = ilDclCache::getRecordCache($original_id);
        $this->setCreateDate($original->getCreateDate());
        $this->setLastEditBy($original->getLastEditBy());
        $this->setLastUpdate($original->getLastUpdate());
        $this->setOwner($original->getOwner());
        $this->doCreate();
        foreach ($new_fields as $old => $new) {
            $old_rec_field = $original->getRecordField($old);
            $new_rec_field = ilDclCache::getRecordFieldCache($this, $new);
            $new_rec_field->cloneStructure($old_rec_field);
            $this->recordfields[] = $new_rec_field;
        }

        // mandatory for all cloning functions
        ilDclCache::setCloneOf($original_id, $this->getId(), ilDclCache::TYPE_RECORD);
    }

    public function deleteFile(int $obj_id): void
    {
        if (ilObject2::_exists($obj_id, false)) {
            $file = new ilObjFile($obj_id, false);
            $file->delete();
        }
    }

    public function deleteMob(int $obj_id): void
    {
        if (ilObject2::_lookupObjId($obj_id)) {
            $mob = new ilObjMediaObject($obj_id);
            $mob->delete();
        }
    }

    public function hasPermissionToEdit(int $ref_id): bool
    {
        return $this->getTable()->hasPermissionToEditRecord($ref_id, $this);
    }

    public function hasPermissionToDelete(int $ref_id): bool
    {
        return $this->getTable()->hasPermissionToDeleteRecord($ref_id, $this);
    }

    public function hasPermissionToView(int $ref_id): bool
    {
        return $this->getTable()->hasPermissionToViewRecord($ref_id, $this);
    }

    /**
     * @return ilDclBaseRecordFieldModel[]
     */
    public function getRecordFields(): array
    {
        $this->loadRecordFields();

        return $this->recordfields;
    }

    public function getTable(): ilDclTable
    {
        $this->loadTable();

        return $this->table;
    }

    /**
     * Get nr of comments of this record
     */
    public function getNrOfComments(): int
    {
        if ($this->nr_of_comments === null) {
            $context = $this->notes
                ->data()
                ->context(
                    $this->table->getCollectionObject()->getId(),
                    $this->getId(),
                    'dcl'
                );
            $this->nr_of_comments = $this->notes
                ->domain()
                ->getNrOfCommentsForContext($context);
        }

        return $this->nr_of_comments;
    }
}
