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
    protected Service $notes;

    /**
     * @var ilDclBaseRecordFieldModel[]
     */
    protected ?array $recordfields = null;
    protected int $id = 0;
    protected int $table_id;
    protected ?ilDclTable $table = null;
    protected int $last_edit_by;
    protected int $owner = 0;
    protected ilDateTime $last_update;
    protected ilDateTime $create_date;
    protected ?int $nr_of_comments = null;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        if ($a_id != 0) {
            $this->id = $a_id;
            $this->doRead();
        }

        $this->notes = $DIC->notes();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    private function fixDate(string $value) : string
    {
        return $value;
    }

    public function doUpdate(bool $omit_notification = false) : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $values = array(
            "table_id" => array(
                "integer",
                $this->getTableId(),
            ),
            "last_update" => array(
                "date",
                $this->fixDate($this->getLastUpdate()),
            ),
            "owner" => array(
                "text",
                $this->getOwner(),
            ),
            "last_edit_by" => array(
                "text",
                $this->getLastEditBy(),
            ),
        );
        $ilDB->update(
            "il_dcl_record",
            $values,
            array(
                "id" => array(
                    "integer",
                    $this->id,
                ),
            )
        );

        foreach ($this->getRecordFields() as $recordfield) {
            $recordfield->doUpdate();
        }

        //TODO: add event raise
        if (!$omit_notification) {
            ilObjDataCollection::sendNotification("update_record", $this->getTableId(), $this->id);
        }
    }

    public function doRead() : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        //build query
        $query = "Select * From il_dcl_record WHERE id = " . $ilDB->quote($this->getId(), "integer") . " ORDER BY id";

        $set = $ilDB->query($query);
        $rec = $ilDB->fetchAssoc($set);

        $this->setTableId($rec["table_id"]);
        $this->setCreateDate($rec["create_date"]);
        $this->setLastUpdate($rec["last_update"]);
        $this->setOwner($rec["owner"]);
        $this->setLastEditBy($rec["last_edit_by"]);
    }

    /**
     * @throws ilException
     */
    public function doCreate() : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (!ilDclTable::_tableExists($this->getTableId())) {
            throw new ilException("The field does not have a related table!");
        }

        $id = $ilDB->nextId("il_dcl_record");
        $this->setId($id);
        $query
            = "INSERT INTO il_dcl_record (
			id,
			table_id,
			create_date,
			Last_update,
			owner,
			last_edit_by
			) VALUES (" . $ilDB->quote($this->getId(), "integer") . "," . $ilDB->quote(
                $this->getTableId(),
                "integer"
            ) . ","
            . $ilDB->quote($this->getCreateDate(), "timestamp") . "," . $ilDB->quote(
                $this->getLastUpdate(),
                "timestamp"
            ) . ","
            . $ilDB->quote($this->getOwner(), "integer") . "," . $ilDB->quote($this->getLastEditBy(), "integer") . "
			)";
        $ilDB->manipulate($query);

        $this->loadRecordFields();
        foreach ($this->getRecordFields() as $recordField) {
            $recordField->doCreate();
        }

        $this->getTable()->loadRecords();
    }

    public function deleteField(int $field_id) : void
    {
        $this->loadRecordFields();
        $this->recordfields[$field_id]->delete();
        if (count($this->recordfields) == 1) {
            $this->doDelete();
        }
    }

    public function setId(int $a_id) : void
    {
        $this->id = $a_id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setTableId(int $a_id) : void
    {
        $this->table_id = $a_id;
    }

    public function getTableId() : int
    {
        return $this->table_id;
    }

    public function setCreateDate(ilDateTime $a_datetime) : void
    {
        $this->create_date = $a_datetime;
    }

    public function getCreateDate() : ilDateTime
    {
        return $this->create_date;
    }

    public function setLastUpdate(ilDateTime $a_datetime) : void
    {
        $this->last_update = $a_datetime;
    }

    public function getLastUpdate() : ilDateTime
    {
        return $this->last_update;
    }

    public function setOwner(int $a_id) : void
    {
        $this->owner = $a_id;
    }

    public function getOwner() : int
    {
        return $this->owner;
    }

    public function getLastEditBy() : string
    {
        return $this->last_edit_by;
    }

    public function setLastEditBy(string $last_edit_by) : void
    {
        $this->last_edit_by = $last_edit_by;
    }

    /**
     * @param int|string $field_id
     * @param int|string $value
     * @return void
     */
    public function setRecordFieldValue($field_id, $value) : void
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
    public function setRecordFieldValueFromForm(int $field_id, ilPropertyFormGUI $form) : void
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
    ) : void {
        $value = $field->getValueFromExcel($excel, $row, $col);
        if ($value) {
            $this->{$field->getId()} = $value;
        }
    }

    public function getRecordFieldValues() : array
    {
        $this->loadRecordFields();
        $return = array();
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
    public function fillRecordFieldExcelExport(ilExcel $worksheet, int &$row, int &$col, $field_id) : void
    {
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            if ($field_id == 'owner') {
                $worksheet->setCell($row, $col, ilObjUser::_lookupLogin($this->getOwner()));
                $col++;
                $name_array = ilObjUser::_lookupName($this->getOwner());
                $worksheet->setCell($row, $col, $name_array['lastname'] . ', ' . $name_array['firstname']);
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
    public function getRecordFieldFormulaValue($field_id) : string
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
    public function getRecordFieldHTML($field_id, array $options = array()) : string
    {
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            $html = $this->getStandardFieldHTML($field_id, $options);
        } else {
            if (is_object($this->recordfields[$field_id])) {
                $html = $this->recordfields[$field_id]->getRecordRepresentation()->getHTML();
            } else {
                $html = '';
            }
        }

        return $html;
    }

    /**
     * @param int|string $field_id
     */
    public function getRecordFieldSortingValue($field_id, array $options = array()) : string
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
    public function getRecordFieldSingleHTML($field_id, array $options = array()) : string
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
    public function fillRecordFieldFormInput($field_id, ilPropertyFormGUI $form) : void
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
    protected function setStandardFieldFromForm($field_id, ilPropertyFormGUI $form) : void
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
    protected function fillStandardFieldFormInput($field_id, ilPropertyFormGUI $form) : void
    {
        if ($item = $form->getItemByPostVar('field_' . $field_id)) {
            $item->setValue($this->getStandardField($field_id));
        }
    }

    /**
     * @param int|string $field_id
     */
    protected function getStandardField($field_id) : string
    {
        switch ($field_id) {
            case "last_edit_by":
                return $this->getLastEditBy();
                break;
            case 'owner':
                $usr_data = ilObjUser::_lookupName($this->getOwner());

                return $usr_data['login'];
                break;
        }

        return $this->{$field_id};
    }

    /**
     * @param int|string $field_id
     */
    public function getStandardFieldFormulaValue($field_id) : string
    {
        return $this->getStandardFieldHTML($field_id);
    }

    public function getStandardFieldHTML(string $field_id, array $options = array()) : string
    {
        switch ($field_id) {
            case 'id':
                return $this->getId();
            case 'owner':
                return ilUserUtil::getNamePresentation($this->getOwner());
            case 'last_edit_by':
                return ilUserUtil::getNamePresentation($this->getLastEditBy());
            case 'last_update':
                return ilDatePresentation::formatDate(new ilDateTime($this->getLastUpdate(), IL_CAL_DATETIME));
            case 'create_date':
                return ilDatePresentation::formatDate(new ilDateTime($this->getCreateDate(), IL_CAL_DATETIME));
            case 'comments':
                $nComments = $this->getNrOfComments();

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
                $ajax_link = ilNoteGUI::getListCommentsJSCall($ajax_hash, '');

                return "<a class='dcl_comment' href='#' onclick=\"return " . $ajax_link . "\">
                        <img src='" . ilUtil::getImagePath("comment_unlabeled.svg")
                    . "' alt='{$nComments} Comments'><span class='ilHActProp'>{$nComments}</span></a>";
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

    private function loadRecordFields() : void
    {
        if ($this->recordfields == null) {
            $this->loadTable();
            $recordfields = array();
            foreach ($this->table->getRecordFields() as $field) {
                if (($recordfields[$field->getId()] ?? null) === null) {
                    $recordfields[$field->getId()] = ilDclCache::getRecordFieldCache($this, $field);
                }
            }

            $this->recordfields = $recordfields;
        }
    }

    private function loadTable() : void
    {
        if ($this->table === null) {
            $this->table = ilDclCache::getTableCache($this->getTableId());
        }
    }

    public function getRecordField(int $field_id) : ilDclBaseRecordFieldModel
    {
        $this->loadRecordFields();

        return $this->recordfields[$field_id];
    }

    public function doDelete(bool $omit_notification = false) : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        $this->loadRecordFields();
        foreach ($this->recordfields as $recordfield) {
            if ($recordfield->getField()->getDatatypeId() == ilDclDatatype::INPUTFORMAT_FILE) {
                $this->deleteFile($recordfield->getValue());
            }

            if ($recordfield->getField()->getDatatypeId() == ilDclDatatype::INPUTFORMAT_MOB) {
                $this->deleteMob($recordfield->getValue());
            }

            $recordfield->delete();
        }

        $query = "DELETE FROM il_dcl_record WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $ilDB->manipulate($query);

        $this->table->loadRecords();

        if (!$omit_notification) {
            ilObjDataCollection::sendNotification("delete_record", $this->getTableId(), $this->getId());

            $ilAppEventHandler->raise(
                'Modules/DataCollection',
                'deleteRecord',
                array(
                    'dcl' => ilDclCache::getTableCache($this->getTableId())->getCollectionObject(),
                    'table_id' => $this->table_id,
                    'record_id' => $this->getId(),
                    'record' => $this,
                )
            );
        }
    }

    // TODO: Find better way to copy data (including all references)
    public function cloneStructure(int $original_id, array $new_fields) : void
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

    public function deleteFile(int $obj_id) : void
    {
        if (ilObject2::_exists($obj_id, false)) {
            $file = new ilObjFile($obj_id, false);
            $file->delete();
        }
    }

    public function deleteMob(int $obj_id) : void
    {
        if (ilObject2::_lookupObjId($obj_id)) {
            $mob = new ilObjMediaObject($obj_id);
            $mob->delete();
        }
    }

    public function hasPermissionToEdit(int $ref_id) : bool
    {
        return $this->getTable()->hasPermissionToEditRecord($ref_id, $this);
    }

    public function hasPermissionToDelete(int $ref_id) : bool
    {
        return $this->getTable()->hasPermissionToDeleteRecord($ref_id, $this);
    }

    public function hasPermissionToView(int $ref_id) : bool
    {
        return $this->getTable()->hasPermissionToViewRecord($ref_id, $this);
    }

    /**
     * @return ilDclBaseRecordFieldModel[]
     */
    public function getRecordFields() : array
    {
        $this->loadRecordFields();

        return $this->recordfields;
    }

    public function getTable() : ilDclTable
    {
        $this->loadTable();

        return $this->table;
    }

    /**
     * Get nr of comments of this record
     */
    public function getNrOfComments() : int
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
