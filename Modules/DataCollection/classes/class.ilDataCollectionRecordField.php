<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/DataCollection/exceptions/class.ilDataCollectionInputException.php';
require_once './Modules/DataCollection/classes/class.ilDataCollectionILIASRefField.php';
require_once './Modules/DataCollection/classes/class.ilDataCollectionReferenceField.php';
require_once 'class.ilDataCollectionRatingField.php';

/**
 * Class ilDataCollectionField
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Marcel Raimann <mr@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionRecordField
{
	protected $id;
    protected $field;
    protected $record;
    protected $value;
	
	/*
	 * __construct
	 */
	public function __construct(ilDataCollectionRecord $record, ilDataCollectionField $field)
	{
		$this->record = $record;
		$this->field = $field;
		$this->doRead();
	}

	/*
	 * doRead
	 */
	private function doRead()
	{
		global $ilDB;
		
		$query = "SELECT * FROM il_dcl_record_field WHERE field_id LIKE ".$ilDB->quote($this->field->getId(), "text")." AND record_id = ".$ilDB->quote($this->record->getId(), "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);
		$this->id = $rec['id'];
		
		if($this->id == NULL)
		{
			$this->doCreate();
		}
	}
	
	/*
	 * doCreate
	 */
	private function doCreate()
	{
		global $ilDB;

		$id = $ilDB->nextId("il_dcl_record_field");
		$query = "INSERT INTO il_dcl_record_field (id, record_id, field_id) VALUES (".$ilDB->quote($id, "integer").", ".$ilDB->quote($this->record->getId(), "integer").", ".$ilDB->quote($this->field->getId(), "text").")";
		$ilDB->manipulate($query);
		$this->id = $id;
	}
	
	/*
	 * doUpdate
	 */
	public function doUpdate()
	{
		global $ilDB;
		
		$this->loadValue();
		$datatype = $this->field->getDatatype();
		$query = "DELETE FROM il_dcl_stloc".$datatype->getStorageLocation()."_value WHERE record_field_id = ".$ilDB->quote($this->id, "integer");
		$ilDB->manipulate($query);
		$next_id = $ilDB->nextId("il_dcl_stloc".$datatype->getStorageLocation()."_value");

		$ilDB->insert("il_dcl_stloc".$datatype->getStorageLocation()."_value",
			array("value" => array($datatype->getDbType(), $this->value),
			"record_field_id " => array("integer", $this->id),
			"id" => array("integer", $next_id))
		);
	}
	
	/*
	 * delete
	 */
	public function delete()
	{
		global $ilDB;

		$datatype = $this->field->getDatatype();
		$query = "DELETE FROM il_dcl_stloc".$datatype->getStorageLocation()."_value WHERE record_field_id = ".$ilDB->quote($this->id, "integer");
		$ilDB->manipulate($query);

		$query2 = "DELETE FROM il_dcl_record_field WHERE id = ".$ilDB->quote($this->id, "integer");
		$ilDB->manipulate($query2);
	}
	
	/*
	 * getValue
	 */
	public function getValue()
	{
		$this->loadValue();
		return $this->value;
	}

	
	/*
	 * setValue
	 */
	public function setValue($value)
	{
		$type = $this->field->getDatatype()->getId();
		$this->loadValue();
		$tmp = $this->field->getDatatype()->parseValue($value, $this);
		$old = $this->value;
		//if parse value fails keep the old value
		if($tmp !== null)
		{
			$this->value = $tmp;
			//delete old file from filesystem
			if($old && $this->field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_FILE)
			{
				$this->record->deleteFile($old);
			}
		}
	}

	/*
	 * getFormInput
	 */
	public function getFormInput()
	{
		$datatype = $this->field->getDatatype();
		
		return $datatype->parseFormInput($this->getValue(), $this);
	}
	
	/*
	 * getExportValue
	 */
	public function getExportValue()
	{
		$datatype = $this->field->getDatatype();

		return $datatype->parseExportValue($this->getValue());
	}

    /**
     * @return mixed used for the sorting.
     */
    public function getPlainText(){
        return $this->getExportValue();
    }

	/*
	 * getHTML
	 */
	public function getHTML()
	{
		$datatype = $this->field->getDatatype();
		return $datatype->parseHTML($this->getValue(), $this);
	}


	/*
	 * loadValue
	 */
	protected function loadValue()
	{
		if($this->value === NULL)
		{
			global $ilDB;
			$datatype = $this->field->getDatatype();
			$query = "SELECT * FROM il_dcl_stloc".$datatype->getStorageLocation()."_value WHERE record_field_id = ".$ilDB->quote($this->id, "integer");
			$set = $ilDB->query($query);
			$rec = $ilDB->fetchAssoc($set);
			$this->value = $rec['value'];
		}
	}
	
	/*
	 * getField
	 */
	public function getField()
	{
		return $this->field;
	}
	
	/*
	 * getId
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/*
	 * getRecord
	 */
	public function getRecord()
	{
		return $this->record;
	}
}
?>
