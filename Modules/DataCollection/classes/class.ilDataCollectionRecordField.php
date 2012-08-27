<?php
/**
 * Created by JetBrains PhpStorm.
 * User: root
 * Date: 8/9/12
 * Time: 9:44 AM
 * To change this template use File | Settings | File Templates.
 */

require_once './Modules/DataCollection/classes/class.ilDataCollectionInputException.php';
require_once 'class.ilDataCollectionRatingField.php';

class ilDataCollectionRecordField
{
	private $id;
	private $field;
	private $record;
	private $value;
	
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
		
		$query = "SELECT * FROM il_dcl_record_field WHERE field_id LIKE '".$this->field->getId()."' AND record_id = ".$this->record->getId();
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
		$query = "INSERT INTO il_dcl_record_field (id, record_id, field_id) VALUES (".$id.", ".$this->record->getId().", ".$this->field->getId().")";
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
		$query = "DELETE FROM il_dcl_stloc".$datatype->getStorageLocation()."_value WHERE record_field_id = ".$this->id;
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
		$query = "DELETE FROM il_dcl_stloc".$datatype->getStorageLocation()."_value WHERE record_field_id = ".$this->id;
		$ilDB->manipulate($query);

		$query2 = "DELETE FROM il_dcl_record_field WHERE id = ".$this->id;
		$ilDB->manipulate($query2);
	}
	
	/*
	 * getValue
	 */
	public function getValue()
	{
		$this->loadValue();
		
		return $this->value ? $this->value : "-";
	}

	
	/*
	 * setValue
	 */
	public function setValue($value)
	{
		$type = $this->field->getDatatype()->getId();
		$this->loadValue();
		$tmp = $this->field->getDatatype()->parseValue($value);
		$old = $this->value;
		//if parse value fails keep the old value
		if($tmp)
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
		
		return $datatype->parseFormInput($this->getValue());
	}
	
	/*
	 * getExportValue
	 */
	public function getExportValue()
	{
		$datatype = $this->field->getDatatype();

		return $datatype->parseExportValue($this->getValue());
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
	private function loadValue()
	{
		if($this->value == NULL)
		{
			global $ilDB;
			$datatype = $this->field->getDatatype();
			$query = "SELECT * FROM il_dcl_stloc".$datatype->getStorageLocation()."_value WHERE record_field_id = ".$this->id;
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

	/**
	 * This method is in order to get the correct class after using it's constructor.
	 * @return ilDataCollectionRecordField If this recordField is a rating you'll get back a ilDaraCollectionRating field, otherwise you get a record Field.
	 */
	public function getInstance(){
		switch($this->getField()->getDatatypeId()){
			case ilDataCollectionDatatype::INPUTFORMAT_RATING:
				return new ilDataCollectionRatingField($this->getRecord(), $this->getField());
			default:
				return $this;
		}
	}

}
?>
