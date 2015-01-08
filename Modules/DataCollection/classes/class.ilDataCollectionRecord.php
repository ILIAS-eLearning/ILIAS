<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/DataCollection/classes/class.ilDataCollectionRecordField.php';
require_once './Modules/DataCollection/classes/class.ilDataCollectionDatatype.php';
require_once './Services/Exceptions/classes/class.ilException.php';
require_once './Services/User/classes/class.ilUserUtil.php';
require_once('./Services/Object/classes/class.ilCommonActionDispatcherGUI.php');
require_once('./Modules/DataCollection/classes/class.ilObjDataCollection.php');
require_once('class.ilDataCollectionTable.php');
require_once('./Services/Notes/classes/class.ilNote.php');
require_once('./Services/Notes/classes/class.ilNoteGUI.php');

/**
* Class ilDataCollectionRecord
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
* @author Oskar Truffer <ot@studer-raimann.ch>
* @author Stefan Wanzenried <sw@studer-raimann.ch>
* @version $Id:
*
* @ingroup ModulesDataCollection
*/
class ilDataCollectionRecord
{
	/**
	 * @var array ilDataCollectionRecordField[]
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
     * @var ilDataCollectionTable
     */
    protected $table;

    /**
     * User ID
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
		if($a_id != 0) 
		{
			$this->id = $a_id;
			$this->doRead();
		}
	}

	
	/**
	 * doUpdate
	 */
	public function doUpdate()
	{
		global $ilDB;

		$ilDB->update("il_dcl_record", array(
			"table_id" => array("integer", $this->getTableId()),
			"last_update" => array("date", $this->getLastUpdate()),
			"owner" => array("text", $this->getOwner()),
			"last_edit_by" => array("text", $this->getLastEditBy())
		), array(
			"id" => array("integer", $this->id)
		));

		foreach($this->getRecordFields() as $recordfield)
		{
			$recordfield->doUpdate();
		}

		ilObjDataCollection::sendNotification("update_record", $this->getTableId(), $this->id);
	}
	
	/**
	 * Read record
	 */
	public function doRead()
	{
		global $ilDB;
		//build query
		$query = "Select * From il_dcl_record WHERE id = ".$ilDB->quote($this->getId(),"integer")." ORDER BY id";

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
		global $ilDB;

		if(!ilDataCollectionTable::_tableExists($this->getTableId()))
			throw new ilException("The field does not have a related table!");

		$id = $ilDB->nextId("il_dcl_record");
		$this->setId($id);
		$query = "INSERT INTO il_dcl_record (
			id,
			table_id,
			create_date,
			Last_update,
			owner,
			last_edit_by
			) VALUES (".
			$ilDB->quote($this->getId(), "integer").",".
			$ilDB->quote($this->getTableId(), "integer").",".
			$ilDB->quote($this->getCreateDate(), "timestamp").",".
			$ilDB->quote($this->getLastUpdate(), "timestamp").",".
			$ilDB->quote($this->getOwner(), "integer").",".
			$ilDB->quote($this->getLastEditBy(), "integer")."
			)";
		$ilDB->manipulate($query);
	}

    /**
     * @param $field_id
     */
    public function deleteField($field_id)
	{
		$this->loadRecordFields();
		$this->recordfields[$field_id]->delete();
        if(count($this->recordfields) == 1)
            $this->doDelete();
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
     * @param int $field_id
     * @param string $value
     */
    public function setRecordFieldValue($field_id, $value)
	{
	   	$this->loadRecordFields();
		if(ilDataCollectionStandardField::_isStandardField($field_id))
		{
			$this->setStandardField($field_id, $value);
		}
		else
		{
			$this->loadTable();
			$this->recordfields[$field_id]->setValue($value);
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
        foreach($this->recordfields as $id => $record_field)
		{
			$return[$id] = $record_field->getValue();
		}
		return $return;
	}
	
	/**
	 * Get Field Value
	 *
	 * @param int $field_id
	 * @return array
	 */
	public function getRecordFieldValue($field_id)
	{
		if ($field_id === null) {
            return null;
        }
        $this->loadRecordFields();
		if(ilDataCollectionStandardField::_isStandardField($field_id))
		{
			return $this->getStandardField($field_id);
		}
		else
		{
            return $this->recordfields[$field_id]->getValue();
		}
	}
	
	
	/**
	 * Get Field Export Value
	 *
	 * @param int $field_id
	 * @return array
	 */
	public function getRecordFieldExportValue($field_id)
	{
		$this->loadRecordFields();
		if(ilDataCollectionStandardField::_isStandardField($field_id))
		{
			return $this->getStandardFieldHTML($field_id);
		}
		else
		{
			return $this->recordfields[$field_id]->getExportValue();
		}
	}


    /**
     * @param $field_id
     * @param array $options
     * @return array|mixed|string
     */
    public function getRecordFieldHTML($field_id,array $options = array())
	{
		$this->loadRecordFields();
		if(ilDataCollectionStandardField::_isStandardField($field_id))
		{
			$html =  $this->getStandardFieldHTML($field_id, $options);
		}
		else
		{
			if (is_object($this->recordfields[$field_id])) {
                $html = $this->recordfields[$field_id]->getHTML();
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
	public function getRecordFieldSingleHTML($field_id, array $options = array()) {
		$this->loadRecordFields();

		if (ilDataCollectionStandardField::_isStandardField($field_id)) {
			$html = $this->getStandardFieldHTML($field_id);
		} else {
			$field = $this->recordfields[$field_id];
			/**
			 * @var $field ilDataCollectionRecordField
			 */
			$html = $field->getSingleHTML($options, false);
		}
		$html = str_ireplace("{", "&#123;", $html);
		$html = str_ireplace("}", "&#125;", $html);

		return $html;
	}


    /**
     * @param $field_id
     * @return int
     */
    public function getRecordFieldFormInput($field_id)
	{
		$this->loadRecordFields();
		if(ilDataCollectionStandardField::_isStandardField($field_id))
		{
			return $this->getStandardField($field_id);
		}
		else
		{
			return $this->recordfields[$field_id]->getFormInput();
        }
	}


    /**
     * @param $field_id
     * @param $value
     */
    protected function setStandardField($field_id, $value)
	{
		switch($field_id)
		{
			case "last_edit_by":
				$this->setLastEditBy($value);
				return;
		}
		$this->$field_id = $value;
	}


    /**
     * @param $field_id
     * @return int
     */
    protected function getStandardField($field_id)
	{
		switch($field_id)
		{
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
	private function getStandardFieldHTML($field_id, array $options = array()) {
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
				$ajax_hash = ilCommonActionDispatcherGUI::buildAjaxHash(1, $_GET['ref_id'], 'dcl', $this->table->getCollectionObject()
						->getId(), 'dcl', $this->getId());
				$ajax_link = ilNoteGUI::getListCommentsJSCall($ajax_hash, '');

				return "<a class='dcl_comment' href='#' onclick=\"return " . $ajax_link . "\">
                        <img src='" . ilUtil::getImagePath("comment_unlabeled.svg")
				. "' alt='{$nComments} Comments'><span class='ilHActProp'>{$nComments}</span></a>";
		}
	}


    /**
     * Load record fields
     */
    private function loadRecordFields()
	{
		if($this->recordfields == NULL)
		{
			$this->loadTable();
			$recordfields = array();
			foreach($this->table->getRecordFields() as $field)
			{
				if($recordfields[$field->getId()] == NULL)
				{
                    $recordfields[$field->getId()] = ilDataCollectionCache::getRecordFieldCache($this, $field);
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
		if($this->table == NULL)
		{
			$this->table = ilDataCollectionCache::getTableCache($this->getTableId());
		}
	}

    /**
     * @param $field_id
     * @return ilDataCollectionRecordField
     */
    public function getRecordField($field_id)
	{
		$this->loadRecordFields();
		
		return $this->recordfields[$field_id];
	}

    /**
     * Delete
     */
    public function doDelete()
	{
		global $ilDB;
		
		$this->loadRecordFields();
		foreach($this->recordfields as $recordfield)
		{
			if($recordfield->getField()->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_FILE)
				$this->deleteFile($recordfield->getValue());

            if($recordfield->getField()->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_MOB)
                $this->deleteMob($recordfield->getValue());

            $recordfield->delete();
		}

		$query = "DELETE FROM il_dcl_record WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);

		ilObjDataCollection::sendNotification("delete_record", $this->getTableId(), $this->getId());
	}


	// TODO: Find better way to copy data (including all references)
    /**
     * @param $original_id integer
     * @param $new_fields array($old_field_id => $new_field)
     */
    /*public function cloneStructure($original_id, $new_fields){
        $original = ilDataCollectionCache::getRecordCache($original_id);
        $this->setCreateDate($original->getCreateDate());
        $this->setLastEditBy($original->getLastEditBy());
        $this->setLastUpdate($original->getLastUpdate());
        $this->setOwner($original->getOwner());
        $this->doCreate();
        foreach($new_fields as $old => $new){
            $old_rec_field = $original->getRecordField($old);
            $new_rec_field = new ilDataCollectionRecordField($this, $new);
            $new_rec_field->setValue($old_rec_field->getValue());
            $new_rec_field->doUpdate();
            $this->recordfields[] = $new_rec_field;
        }
    }*/


    /**
     * Delete a file
     *
     * @param $obj_id
     */
    public function deleteFile($obj_id)
	{
        if(ilObject2::_lookupObjId($obj_id)){
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
        if(ilObject2::_lookupObjId($obj_id)){
            $mob = new ilObjMediaObject($obj_id);
            $mob->delete();
        }
    }

    /**
     * @param array $filter
     * @return bool
     */
    public function passThroughFilter(array $filter)
	{
		$this->loadTable();
		// If one field returns false, the whole record does not pass the filter #performance-improvements
        foreach ($this->table->getFilterableFields() as $field) {
            if (!isset($filter["filter_" . $field->getId()]) || !$filter["filter_" . $field->getId()]) continue;
            if(!ilDataCollectionDatatype::passThroughFilter($this, $field, $filter["filter_".$field->getId()]))
			{
                return false;
			}
		}
		return true;
	}

    /**
     * @param int $ref_id
     * @return bool
     */
    public function hasPermissionToEdit($ref_id)
	{
		return $this->getTable()->hasPermissionToEditRecord($ref_id, $this);
	}

    /**
     * @param int $ref_id
     * @return bool
     */
    public function hasPermissionToDelete($ref_id)
	{
		return $this->getTable()->hasPermissionToDeleteRecord($ref_id, $this);
	}

    /**
     * @param $ref_id
     * @return bool
     */
    public function hasPermissionToView($ref_id) {
        return $this->getTable()->hasPermissionToViewRecord($ref_id, $this);
    }

    /**
     * @return array
     */
    public function getRecordFields()
	{
		$this->loadRecordFields();
		return $this->recordfields;
	}

	/**
	 * @return ilDataCollectionTable
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
    public function getComments() {
        if ($this->comments === null) {
            $this->comments = ilNote::_getNotesOfObject($this->table->getCollectionObject()->getId(), $this->getId(), 'dcl', IL_NOTE_PUBLIC);
        }
        return $this->comments;
    }
}
?>