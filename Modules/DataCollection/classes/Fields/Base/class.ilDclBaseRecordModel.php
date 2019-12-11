<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/DataCollection/classes/Fields/Base/class.ilDclBaseRecordFieldModel.php';
require_once './Modules/DataCollection/classes/Fields/Base/class.ilDclDatatype.php';
require_once './Services/Exceptions/classes/class.ilException.php';
require_once './Services/User/classes/class.ilUserUtil.php';

/**
 * Class ilDclBaseRecordModel
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
class ilDclBaseRecordModel
{

    /**
     * @var ilDclBaseRecordFieldModel[]
     */
    protected $recordfields;
    /**
     * @var int
     */
    protected $id;
    /**
     * @var int
     */
    protected $table_id;
    /**
     * @var ilDclTable
     */
    protected $table;
    /**
     * User ID
     *
     * @var int
     */
    protected $last_edit_by;
    /**
     * @var int
     */
    protected $owner;
    /**
     * @var ilDateTime
     */
    protected $last_update;
    /**
     * @var ilDateTime
     */
    protected $create_date;
    /**
     * @var array ilNote[]
     */
    protected $comments;


    /**
     * @param int $a_id
     */
    public function __construct($a_id = 0)
    {
        if ($a_id != 0) {
            $this->id = $a_id;
            $this->doRead();
        }
    }


    /**
     * @param $value
     *
     * @return string
     */
    private function fixDate($value)
    {
        return $value;
    }


    /**
     * doUpdate
     */
    public function doUpdate($omit_notification = false)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $values = array(
            "table_id"     => array(
                "integer",
                $this->getTableId(),
            ),
            "last_update"  => array(
                "date",
                $this->fixDate($this->getLastUpdate()),
            ),
            "owner"        => array(
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


    /**
     * Read record
     */
    public function doRead()
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
    public function doCreate()
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
			) VALUES (" . $ilDB->quote($this->getId(), "integer") . "," . $ilDB->quote($this->getTableId(), "integer") . ","
            . $ilDB->quote($this->getCreateDate(), "timestamp") . "," . $ilDB->quote($this->getLastUpdate(), "timestamp") . ","
            . $ilDB->quote($this->getOwner(), "integer") . "," . $ilDB->quote($this->getLastEditBy(), "integer") . "
			)";
        $ilDB->manipulate($query);

        $this->loadRecordFields();
        foreach ($this->getRecordFields() as $recordField) {
            $recordField->doCreate();
        }

        $this->getTable()->loadRecords();
    }


    /**
     * @param $field_id
     */
    public function deleteField($field_id)
    {
        $this->loadRecordFields();
        $this->recordfields[$field_id]->delete();
        if (count($this->recordfields) == 1) {
            $this->doDelete();
        }
    }


    /**
     * Set field id
     *
     * @param int $a_id
     */
    public function setId($a_id)
    {
        $this->id = $a_id;
    }


    /**
     * Get field id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set Table ID
     *
     * @param int $a_id
     */
    public function setTableId($a_id)
    {
        $this->table_id = $a_id;
    }


    /**
     * Get Table ID
     *
     * @return int
     */
    public function getTableId()
    {
        return $this->table_id;
    }


    /**
     * Set Creation Date
     *
     * @param ilDateTime $a_datetime
     */
    public function setCreateDate($a_datetime)
    {
        $this->create_date = $a_datetime;
    }


    /**
     * Get Creation Date
     *
     * @return ilDateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }


    /**
     * Set Last Update Date
     *
     * @param ilDateTime $a_datetime
     */
    public function setLastUpdate($a_datetime)
    {
        $this->last_update = $a_datetime;
    }


    /**
     * Get Last Update Date
     *
     * @return ilDateTime
     */
    public function getLastUpdate()
    {
        return $this->last_update;
    }


    /**
     * Set Owner
     *
     * @param int $a_id
     */
    public function setOwner($a_id)
    {
        $this->owner = $a_id;
    }


    /**
     * Get Owner
     *
     * @return int
     */
    public function getOwner()
    {
        return $this->owner;
    }


    /*
     * getLastEditBy
     */
    public function getLastEditBy()
    {
        return $this->last_edit_by;
    }


    /*
     * setLastEditBy
     */
    public function setLastEditBy($last_edit_by)
    {
        $this->last_edit_by = $last_edit_by;
    }


    /**
     * Set a field value
     *
     * @param int    $field_id
     * @param string $value
     */
    public function setRecordFieldValue($field_id, $value)
    {
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            $this->setStandardField($field_id, $value);
        } else {
            $this->loadTable();
            $record_field = $this->recordfields[$field_id];

            $this->recordfields[$field_id]->setValue($value);
        }
    }


    /**
     * Set a field value
     *
     * @param int    $field_id
     * @param string $value
     */
    public function setRecordFieldValueFromForm($field_id, &$form)
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
     * @param $excel ilExcel
     * @param $row
     * @param $col
     * @param $field ilDclBaseFieldModel
     *
     * @return array|string
     */
    public function getRecordFieldValueFromExcel($excel, $row, $col, $field)
    {
        $this->loadRecordFields();

        return $this->recordfields[$field->getId()]->getValueFromExcel($excel, $row, $col);
    }


    /**
     * @param $excel ilExcel
     * @param $row
     * @param $col
     * @param $field ilDclStandardField
     */
    public function setStandardFieldValueFromExcel($excel, $row, $col, $field)
    {
        $value = $field->getValueFromExcel($excel, $row, $col);
        if ($value) {
            $this->{$field->getId()} = $value;
        }
    }


    /**
     * @deprecated
     * @return array
     */
    public function getRecordFieldValues()
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
     *
     * @param int $field_id
     *
     * @return array
     */
    public function getRecordFieldValue($field_id)
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
     *
     * @param $field_id
     *
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
     *
     * @param int $field_id
     *
     * @return array
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
     *
     * @param int $field_id
     *
     * @return array
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
     * @param $worksheet
     * @param $row
     * @param $col
     * @param $field_id
     */
    public function fillRecordFieldExcelExport(ilExcel $worksheet, &$row, &$col, $field_id)
    {
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            if ($field_id == 'owner') {
                $worksheet->setCell($row, $col, ilObjUser::_lookupLogin($this->getOwner()));
                $col++;
                $name_array = ilObjUser::_lookupName($this->getOwner());
                $worksheet->setCell($row, $col, $name_array['lastname'] . ', ' . $name_array['firstname']);
                $col++;
            } else {
                $worksheet->setCell($row, $col, $this->getStandardFieldHTML($field_id));
                $col++;
            }
        } else {
            $this->recordfields[$field_id]->fillExcelExport($worksheet, $row, $col);
        }
    }


    /**
     * @param       $field_id
     * @param array $options
     *
     * @return array|mixed|string
     */
    public function getRecordFieldHTML($field_id, array $options = array())
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

        // This is a workaround as templating in ILIAS currently has some issues with curly brackets.see: http://www.ilias.de/mantis/view.php?id=12681#bugnotes
        // SW 16.07.2014 Uncommented again, as some fields are outputting javascript that was broken due to entity encode the curly brackets
        //		$html = str_ireplace("{", "&#123;", $html);
        //		$html = str_ireplace("}", "&#125;", $html);

        return $html;
    }


    /**
     * @param       $field_id
     * @param array $options
     *
     * @return array|mixed|string
     */
    public function getRecordFieldSortingValue($field_id, array $options = array())
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

        // This is a workaround as templating in ILIAS currently has some issues with curly brackets.see: http://www.ilias.de/mantis/view.php?id=12681#bugnotes
        // SW 16.07.2014 Uncommented again, as some fields are outputting javascript that was broken due to entity encode the curly brackets
        //		$html = str_ireplace("{", "&#123;", $html);
        //		$html = str_ireplace("}", "&#125;", $html);

        return $html;
    }


    /**
     * @param       $field_id
     * @param array $options
     *
     * @return array|string
     */
    public function getRecordFieldSingleHTML($field_id, array $options = array())
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
        // This is a workaround as templating in ILIAS currently has some issues with curly brackets.see: http://www.ilias.de/mantis/view.php?id=12681#bugnotes
        // SW 14.10.2015 Uncommented again, as some fields are outputting javascript that was broken due to entity encode the curly brackets
        //		$html = str_ireplace("{", "&#123;", $html);
        //		$html = str_ireplace("}", "&#125;", $html);

        return $html;
    }


    /**
     * @param $field_id
     * @param $form ilPropertyFormGUI
     */
    public function fillRecordFieldFormInput($field_id, &$form)
    {
        $this->loadRecordFields();
        if (ilDclStandardField::_isStandardField($field_id)) {
            $this->fillStandardFieldFormInput($field_id, $form);
        } else {
            $this->recordfields[$field_id]->getRecordRepresentation()->fillFormInput($form);
        }
    }


    /**
     * @param                   $field_id
     * @param ilPropertyFormGUI $form
     */
    protected function setStandardFieldFromForm($field_id, &$form)
    {
        if ($item = $form->getItemByPostVar("field_" . $field_id)) {
            $this->setStandardField($item->getValue());
        }
    }


    /**
     * @param $field_id
     * @param $value
     */
    protected function setStandardField($field_id, $value)
    {
        switch ($field_id) {
            case "last_edit_by":
                $this->setLastEditBy($value);

                return;
        }
        $this->$field_id = $value;
    }


    /**
     * @param $field_id
     * @param $form
     */
    protected function fillStandardFieldFormInput($field_id, &$form)
    {
        if ($item = $form->getItemByPostVar('field_' . $field_id)) {
            $item->setValue($this->getStandardField($field_id));
        }
    }


    /**
     * @param $field_id
     *
     * @return int
     */
    protected function getStandardField($field_id)
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

        return $this->$field_id;
    }


    /**
     * @param string $field_id
     * @param array  $options
     *
     * @return array|string
     */
    public function getStandardFieldHTML($field_id, array $options = array())
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
                $nComments = count($this->getComments());
                $ajax_hash = ilCommonActionDispatcherGUI::buildAjaxHash(
                    1,
                    $_GET['ref_id'],
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
    }

    /**
     * @param string $field_id
     *
     * @return array|string
     */
    public function getStandardFieldPlainText($field_id)
    {
        switch ($field_id) {
            case 'comments':
                return count(ilNote::_getNotesOfObject(
                    $this->table->getCollectionObject()->getId(),
                    $this->getId(),
                    "dcl",
                    2,
                    false,
                    "",
                    "y",
                    true,
                    true
                ));
            default:
                return strip_tags($this->getStandardFieldHTML($field_id));
        }
    }


    /**
     * Load record fields
     */
    private function loadRecordFields()
    {
        if ($this->recordfields == null) {
            $this->loadTable();
            $recordfields = array();
            foreach ($this->table->getRecordFields() as $field) {
                if ($recordfields[$field->getId()] == null) {
                    $recordfields[$field->getId()] = ilDclCache::getRecordFieldCache($this, $field);
                }
            }

            $this->recordfields = $recordfields;
        }
    }


    /**
     * Load table
     */
    private function loadTable()
    {
        if ($this->table == null) {
            $this->table = ilDclCache::getTableCache($this->getTableId());
        }
    }


    /**
     * @param $field_id
     *
     * @return ilDclBaseRecordFieldModel
     */
    public function getRecordField($field_id)
    {
        $this->loadRecordFields();

        return $this->recordfields[$field_id];
    }


    /**
     * Delete
     *
     * @param bool $omit_notification
     */
    public function doDelete($omit_notification = false)
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
                    'dcl'       => ilDclCache::getTableCache($this->getTableId())->getCollectionObject(),
                    'table_id'  => $this->table_id,
                    'record_id' => $this->getId(),
                    'record'    => $this,
                )
            );
        }
    }


    // TODO: Find better way to copy data (including all references)


    /**
     * @param $original_id integer
     * @param $new_fields  array($old_field_id => $new_field)
     */
    public function cloneStructure($original_id, $new_fields)
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


    /**
     * Delete a file
     *
     * @param $obj_id
     */
    public function deleteFile($obj_id)
    {
        if (ilObject2::_exists($obj_id, false)) {
            $file = new ilObjFile($obj_id, false);
            $file->delete();
        }
    }


    /**
     * Delete MOB
     *
     * @param $obj_id
     */
    public function deleteMob($obj_id)
    {
        if (ilObject2::_lookupObjId($obj_id)) {
            $mob = new ilObjMediaObject($obj_id);
            $mob->delete();
        }
    }


    /**
     * @param array $filter
     *
     * @return bool
     */
    public function passThroughFilter(array $filter)
    {
        $this->loadTable();
        // If one field returns false, the whole record does not pass the filter #performance-improvements
        foreach ($this->table->getFilterableFields() as $field) {
            if (!isset($filter["filter_" . $field->getId()]) || !$filter["filter_" . $field->getId()]) {
                continue;
            }
            if (!ilDclCache::getFieldRepresentation($field)->passThroughFilter($this, $filter["filter_" . $field->getId()])) {
                return false;
            }
        }

        return true;
    }


    /**
     * @param int $ref_id
     *
     * @return bool
     */
    public function hasPermissionToEdit($ref_id)
    {
        return $this->getTable()->hasPermissionToEditRecord($ref_id, $this);
    }


    /**
     * @param int $ref_id
     *
     * @return bool
     */
    public function hasPermissionToDelete($ref_id)
    {
        return $this->getTable()->hasPermissionToDeleteRecord($ref_id, $this);
    }


    /**
     * @param $ref_id
     *
     * @return bool
     */
    public function hasPermissionToView($ref_id)
    {
        return $this->getTable()->hasPermissionToViewRecord($ref_id, $this);
    }


    /**
     * @return ilDclBaseRecordFieldModel[]
     */
    public function getRecordFields()
    {
        $this->loadRecordFields();

        return $this->recordfields;
    }


    /**
     * @return ilDclTable
     */
    public function getTable()
    {
        $this->loadTable();

        return $this->table;
    }


    /**
     * Get all comments of this record
     *
     * @return array ilNote[]
     */
    public function getComments()
    {
        if ($this->comments === null) {
            $this->comments = ilNote::_getNotesOfObject($this->table->getCollectionObject()->getId(), $this->getId(), 'dcl', IL_NOTE_PUBLIC);
        }

        return $this->comments;
    }
}
