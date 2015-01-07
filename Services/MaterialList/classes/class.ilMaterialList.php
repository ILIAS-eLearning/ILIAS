<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMaterialList
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
 * @ingroup ServicesMaterialList
 */
class ilMaterialList
{
	protected $parent_id; // [int]
	protected $id; // [int]
	protected $quantity_participant; // [int]
	protected $quantity_parent; // [int]
	protected $material_number; // [text]
	protected $description; // [text]
	
	/**
	 * Constructor
	 * 
	 * @param int $a_parent_id
	 * @param int $a_id
	 * @return object
	 */
	public function __construct($a_parent_id, $a_id = null)
	{		
		$this->parent_id = $a_parent_id;
		
		if($a_id > 0)
		{
			$this->id = $a_id;		
			$this->read($this->id);
		}
	}
	
	
	//
	// properties
	//
	
	/**
	 * Set quantity per participant
	 * 
	 * @param int $a_value
	 */
	public function setQuantityParticipant($a_value)
	{
		$this->quantity_participant = (int)$a_value;
	}
	
	/**
	 * Get quantity per participant
	 * 
	 * @return int 
	 */
	public function getQuantityParticipant()
	{
		return (int)$this->quantity_participant;
	}
	
	/**
	 * Set quantity per parent object
	 * 
	 * @param int $a_value
	 */
	public function setQuantityParent($a_value)
	{
		$this->quantity_parent = (int)$a_value;
	}
	
	/**
	 * Get quantity per parent object
	 * 
	 * @return int
	 */
	public function getQuantityParent()
	{
		return (int)$this->quantity_parent;
	}
	
	/**
	 * Set material number/id
	 * 
	 * @param string $a_value
	 */
	public function setMaterialNumber($a_value)
	{
		$this->material_number = (string)$a_value;
	}
	
	/**
	 * Get material number/id
	 * 
	 * @return string
	 */
	public function getMaterialNumber()
	{
		return (string)$this->material_number;
	}
	
	/**
	 * Set description/title
	 * 
	 * @param string $a_value
	 */
	public function setDescription($a_value)
	{
		$this->description = (string)$a_value;
	}
	
	/**
	 * Get description/title
	 * 
	 * @return string 
	 */
	public function getDescription()
	{
		return (string)$this->description;
	}
	
	
	//
	// CRUD
	// 
	
	/**
	 * Read entry from DB
	 * 
	 * @param int $a_id
	 */
	protected function read($a_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM crs_matlist".
			" WHERE id = ".$ilDB->quote($a_id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		if($row["id"])
		{
			$this->id = $row["id"];
			$this->parent_id = $row["obj_id"];
			$this->setQuantityParticipant($row["quant_per_part"]);
			$this->setQuantityParent($row["quant_per_crs"]);
			$this->setMaterialNumber($row["mat_number"]);
			$this->setDescription($row["description"]);			
		}		
	}
	
	/**
	 * Prepare properties for sql query
	 * 
	 * @return array
	 */
	protected function getPropertiesAsSql()
	{
		global $ilUser;
		
		$fields = array(
			"obj_id" => array("integer", $this->parent_id),
			"quant_per_part" => array("integer", $this->getQuantityParticipant()),
			"quant_per_crs" => array("integer", $this->getQuantityParent()),
			"mat_number" => array("text", $this->getMaterialNumber()),
			"description" => array("text", $this->getDescription()),			
			"changed_by" => array("integer", $ilUser->getId()),			
			"changed_on" => array("integer", time()),			
		);
		return $fields;
	}
	
	/**
	 * Create new entry
	 */
	public function save()
	{
		global $ilDB;
		
		if($this->id)
		{
			return;
		}
		
		$this->id = $ilDB->nextId("crs_matlist"); 
		
		$fields = $this->getPropertiesAsSql();
		$fields["id"] = array("integer", $this->id);
		
		$ilDB->insert("crs_matlist", $fields);
	}
	
	/**
	 * Update existing entry
	 */
	public function update()
	{
		global $ilDB;
		
		if(!$this->id)
		{
			return;
		}
		
		$fields = $this->getPropertiesAsSql();
		
		$ilDB->update("crs_matlist", $fields, array("id"=>array("integer", $this->id)));
	}
	
	/**
	 * Delete existing entry
	 */
	public function delete()
	{
		global $ilDB;
		
		if(!$this->id)
		{
			return;
		}
		
		$ilDB->manipulate("DELETE FROM crs_matlist".
			" WHERE id = ".$ilDB->quote($this->id, "integer"));
		$this->id = null;
	}
	
	
	//
	// logic
	//
	
	/**
	 * Get item list title
	 * 
	 * @param int $a_id
	 * @return stringe
	 */	
	public static function lookupListTitle($a_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT mat_number,description".
			" FROM crs_matlist".
			" WHERE id = ".$ilDB->quote($a_id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		return $row["mat_number"]." - ".$row["description"];
	}
		
	
	//
	// list
	// 	
	
	/**
	 * Get last change info 
	 * 
	 * @return array
	 */
	public function getLastChange()
	{
		global $ilDB;
		
		$ilDB->setLimit(1);
		
		$set = $ilDB->query("SELECT changed_by, changed_on".
			" FROM crs_matlist".
			" WHERE obj_id = ".$ilDB->quote($this->parent_id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		if($row["changed_by"])
		{
			return array(
				"user_id" => $row["changed_by"]
				,"changed_date" => new ilDate($row["changed_on"], IL_CAL_UNIX)
			);
		}
	}
		
	/**
	 * Check if there are any items for parent object
	 * 
	 * @param type $a_obj_id
	 * @return bool
	 */
	public static function hasItems($a_obj_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM crs_matlist".
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer"));
		return (bool)$ilDB->numRows($set);
	}
	
	/**
	 * Get all entries for parent object
	 * 
	 * @return array
	 */
	public static function getRawList($a_obj_id)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT * FROM crs_matlist".
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{					
			$res[$row["id"]] = array(
				"amount_per_course" => (int)$row["quant_per_crs"]
				,"amount_per_participant" => (int)$row["quant_per_part"]
				,"item_number" => trim($row["mat_number"])
				,"description" => trim($row["description"])
			);
		}
		return $res;
	}
	
	/**
	 * Get instance of material list helper
	 * 
	 * @return ilMaterialListHelper
	 */
	protected function getHelperInstance()
	{
		include_once "Services/MaterialList/classes/class.ilMaterialListHelper.php";
		return ilMaterialListHelper::getInstance($this->parent_id);
	}
		
	/**
	 * Get all entries for parent object
	 * 
	 * @return array
	 */
	public function getList()
	{		
		$res = array();
				
		$nr_of_participants = $this->getHelperInstance()->getAmountOfParticipants();
		
		foreach(self::getRawList($this->parent_id) as $id => $item)
		{
			$amount = $item["amount_per_course"] +
				($item["amount_per_participant"] * $nr_of_participants);
			
			$res[$id] = array(				
				"item_number" => $item["item_number"]
				,"description" =>  $item["description"]
				,"amount" => $amount
			);
		}
		
		return $res;
	}		
	
	/**
	 * Delete all existing entries
	 * 
	 * @param int $a_obj_id
	 */
	public static function deleteList($a_obj_id)
	{
		// remove existing entries
		foreach(array_keys(self::getRawList($a_obj_id)) as $item_id)
		{
			$target_item = new self($a_obj_id, $item_id);
			$target_item->delete();
		}
	}
	
	/**
	 * Copy all list items for target object id
	 * 
	 * @param int $a_target_obj_id
	 */
	public function copyTo($a_target_obj_id)
	{
		self::deleteList($a_target_obj_id);
		
		// copy entries
		foreach(self::getRawList($this->parent_id) as $item)
		{
			$target_item = new self($a_target_obj_id);
			$target_item->setMaterialNumber($item["item_number"]);
			$target_item->setDescription($item["description"]);
			$target_item->setQuantityParent($item["amount_per_course"]);
			$target_item->setQuantityParticipant($item["amount_per_participant"]);
			
			// changed_on, changed_by will be set to now / current user
			$target_item->save();
		}
	}
	
	
	//
	// export
	//
	
	/**
	 * Get general information
	 * 
	 * @return array
	 */
	public function getGeneralInformation()
	{
		global $lng;
		
		$res = array();
		
		$res[$lng->txt("matlist_xls_title")] = ilObject::_lookupTitle($this->parent_id);
		$res[$lng->txt("matlist_xls_subtitle")] = ilObject::_lookupDescription($this->parent_id);
		
		$helper = $this->getHelperInstance();
		$res[$lng->txt("matlist_xls_custom_id")] = $helper->getCustomId();
		$res[$lng->txt("matlist_xls_amount_participants")] = $helper->getAmountOfParticipants();
		$res[$lng->txt("matlist_xls_date_info")] = $helper->getDateInfo();
		$res[$lng->txt("matlist_xls_trainer")] = $helper->getTrainer();
		$res[$lng->txt("matlist_xls_venue_info")] = $helper->getVenueInfo();
		$res[$lng->txt("matlist_xls_contact")] = $helper->getContact();
		
		ilDatePresentation::setUseRelativeDates(false);
		$res[$lng->txt("matlist_xls_creation_date")] = ilDatePresentation::formatDate(new ilDate(time(), IL_CAL_UNIX));
		
		return $res;
	}
	
	/**
	 * Build XLS meta block
	 * 
	 * @param object $a_workbook
	 * @param object $a_worksheet
	 * @param string $a_header
	 * @param string $a_general_header
	 * @param string $a_item_header
	 * @param array $column_titles
	 * @return int
	 */
	protected function buildXLSMeta($a_workbook, $a_worksheet, $a_header, $a_general_header, $a_item_header, array $column_titles)
	{				
		$num_cols = sizeof($column_titles);

		$format_bold = $a_workbook->addFormat(array("bold" => 1));
		$format_title = $a_workbook->addFormat(array("bold" => 1, "size" => 14));
		$format_subtitle = $a_workbook->addFormat(array("bold" => 1, "bottom" => 6));

		$a_worksheet->writeString(0, 0, $a_header, $format_title);
		$a_worksheet->mergeCells(0, 0, 0, $num_cols-1);
		$a_worksheet->mergeCells(1, 0, 1, $num_cols-1);

		$a_worksheet->writeString(2, 0, $a_general_header, $format_subtitle);
		for($loop = 1; $loop < $num_cols; $loop++)
		{
			$a_worksheet->writeString(2, $loop, "", $format_subtitle);
		}
		$a_worksheet->mergeCells(2, 0, 2, $num_cols-1);
		$a_worksheet->mergeCells(3, 0, 3, $num_cols-1);

		// course info
		$row = 4;
		foreach($this->getGeneralInformation() as $caption => $value)
		{
			$a_worksheet->writeString($row, 0, $caption, $format_bold);

			if(!is_array($value))
			{
				$a_worksheet->writeString($row, 1, $value);
				$a_worksheet->mergeCells($row, 1, $row, $num_cols-1);
			}
			else
			{
				$first = array_shift($value);
				$a_worksheet->writeString($row, 1, $first);
				$a_worksheet->mergeCells($row, 1, $row, $num_cols-1);

				foreach($value as $line)
				{
					if(trim($line))
					{
						$row++;
						$a_worksheet->write($row, 0, "");
						$a_worksheet->writeString($row, 1, $line);
						$a_worksheet->mergeCells($row, 1, $row, $num_cols-1);
					}
				}
			}

			$row++;
		}

		// empty row
		$a_worksheet->mergeCells($row, 0, $row, $num_cols-1);
		$row++;
		$a_worksheet->mergeCells($row, 0, $row, $num_cols-1);
		$row++;

		// row_title
		$a_worksheet->writeString($row, 0, $a_item_header, $format_subtitle);
		for($loop = 1; $loop < $num_cols; $loop++)
		{
			$a_worksheet->writeString($row, $loop, "", $format_subtitle);
		}
		$a_worksheet->mergeCells($row, 0, $row, $num_cols-1);
		$row++;
		$a_worksheet->mergeCells($row, 0, $row, $num_cols-1);
		$row++;

		// title row
		for($loop = 0; $loop < $num_cols; $loop++)
		{
			$a_worksheet->writeString($row, $loop, $column_titles[$loop], $format_bold);
		}

		return $row;
	}

	/**
	 * Build XLS file
	 *
	 * @param string $a_filename
	 * @param bool $a_send
	 */
	public function buildXLS($a_filename, $a_send = true)
	{
		global $lng;
		
		
		// gev-patch start
		$lng->loadLanguageModule("matlist");
		//$filename = ilUtil::getASCIIFilename($a_filename).".xls";
		// gev-patch end
		
		include_once "./Services/Excel/classes/class.ilExcelUtils.php";
		include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
		$adapter = new ilExcelWriterAdapter($a_filename, $a_send);
		$workbook = $adapter->getWorkbook();
		$worksheet = $workbook->addWorksheet();
		$worksheet->setLandscape();

		$worksheet->setColumn(0, 0, 20);
		$worksheet->setColumn(1, 1, 40);
		$worksheet->setColumn(2, 2, 20);

		$row = $this->buildXLSMeta(
			$workbook, 
			$worksheet,
			$lng->txt("matlist_xls_list_header"),
			$lng->txt("matlist_xls_list_general_header"),
			$lng->txt("matlist_xls_list_item_header"),
			array($lng->txt("matlist_product_id"),
				$lng->txt("matlist_title"),
				$lng->txt("matlist_course_count"))
			);
		
		$format_wrap = $workbook->addFormat();
		$format_wrap->setTextWrap();

		foreach($this->getList() as $item)
		{
			$row++;

			$worksheet->writeString($row, 0, $item["item_number"], $format_wrap);
			$worksheet->writeString($row, 1, $item["description"], $format_wrap);
			$worksheet->write($row, 2, $item["amount"], $format_wrap);
		}

		$workbook->close();
		if($a_send)
		{
			exit();
		}
	}	
}
