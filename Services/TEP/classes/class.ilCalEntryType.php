<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Calendar entry type application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 */
class ilCalEntryType 
{
	protected $id; // [string]
	protected $title; // [string]
	protected $bg_color; // [htmlcolor]
	protected $font_color; // [htmlcolor]
	protected $tep_gui_active; // [bool]
	
	/**
	 * Constructor
	 * 
	 * @param int $a_id
	 * @return self
	 */
	public function __construct($a_id = null)
	{
		if($a_id)
		{
			$this->setId($a_id);
			$this->read();
		}
	}
	
	
	//
	// properties
	//
	
	/**
	 * Set id
	 * 
	 * @param string $a_id
	 */
	protected function setId($a_id)
	{
		$this->id = (string)$a_id;
	}
	
	/**
	 * Get id
	 * 
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Set title
	 * 
	 * @param string $a_value
	 */
	protected function setTitle($a_value)
	{
		$this->title = trim($a_value);
	}
	
	/**
	 * Get title
	 * 
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * Set background color
	 * 
	 * @param string $a_value
	 */
	protected function setBackgroundColor($a_value)
	{
		if(self::isValidColor($a_value))
		{
			$this->bg_color = $a_value;
		}
	}
	
	/**
	 * Get background color
	 * 
	 * @return string
	 */
	public function getBackgroundColor()
	{
		return $this->bg_color;
	}
	
	/**
	 * Set font color
	 * 
	 * @param string $a_value
	 */
	protected function setFontColor($a_value)
	{
		if(self::isValidColor($a_value))
		{
			$this->font_color = $a_value;
		}
	}
	
	/**
	 * Get font color
	 * 
	 * @return string
	 */
	public function getFontColor()
	{
		// if font color is undefined determine matching color for background
		if(!$this->font_color)
		{
			$bg_color = $this->getBackgroundColor();
			if($bg_color)
			{
				return self::getFontColorForBg($bg_color);
			}			
		}
		return $this->font_color;
	}
	
	/**
	 * Set TEP GUI status
	 * 
	 * @param bool $a_value
	 */
	protected function setTEPGUIActive($a_value)
	{		
		$this->tep_gui_active = (bool)$a_value;		
	}
	
	/**
	 * Get TEP GUI status
	 * 
	 * @return bool
	 */
	public function getTEPGUIActive()
	{
		return $this->tep_gui_active;
	}
	
	
	//
	// CRUD
	// 
	
	/**
	 * Read from DB
	 * 
	 * @param string $a_id
	 * @return boolean
	 */
	protected function read()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return;
		}
		
		$sql = "SELECT * FROM tep_type".
			" WHERE id = ".$ilDB->quote($this->getId(), "text");
		$set = $ilDB->query($sql);
		if($ilDB->numRows($set))
		{
			$values = $ilDB->fetchAssoc($set);		
			$this->setId($values["id"]);
			$this->setTitle($values["title"]);
			$this->setBackgroundColor($values["bg_color"]);
			$this->setFontColor($values["font_color"]);
			$this->setTEPGUIActive($values["tep_active"]);
			return true;
		}

		return false;
	}
	
	/**
	 * Get properties for DB
	 * 
	 * @return array
	 */
	protected function getDBFields()
	{
		return array(
			"title" => array("text", $this->getTitle())
			,"bg_color" => array("text", $this->getBackgroundColor())
			,"font_color" => array("text", $this->getFontColor())
			,"tep_active" => array("integer", $this->getTEPGUIActive())
		);
	}

	/**
	 * Create DB entry
	 * 
	 * @return boolean
	 */
	public function save()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return;
		}
		
		$fields = $this->getDBFields();
		$fields["id"] = $this->getId();
		
		$ilDB->insert("tep_type", $fields);
		return true;
	}

	/**
	 * Update DB entry
	 * 
	 * @return boolean
	 */
	public function update()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return;
		}
		
		$fields = $this->getDBFields();
		
		$ilDB->insert("tep_type", $fields,
			array("id"=>array("text", $this->getId())));
		return true;
	}

	/**
	 * Delete DB entry
	 */
	public function delete()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return;
		}
		
		$ilDB->manipulate("DELETE * FROM tep_type".
			" WHERE id = ".$ilDB->quote($this->getId(), "text"));
	}

	
	//
	// presentation
	// 

	/**
	 * Get all active types for presentation
	 * 
	 * @return array
	 */
	public static function getAllActive()
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT * FROM tep_type".
			" WHERE tep_active = ".$ilDB->quote(1, "integer");
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[trim($row["id"])] = trim($row["title"]);
		}
		
		return $res;
	}
	
	public static function getAll() {
		global $ilDB;
		
		$ret = array();
		$res = $ilDB->query("SELECT id FROM tep_type");
		while ($rec = $ilDB->fetchAssoc($res)) {
			$ret[] = $rec["id"];
		}
		return $ret;
	}
	
	/**
	 * Get complete entry data
	 *
	 * @param array $a_ids
	 * @return array
	 */
	public static function getListData(array $a_ids = null)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT * FROM tep_type";
		if(is_array($a_ids))
		{
			$sql .= " WHERE ".$ilDB->in("id", $a_ids, "", "text");
		}
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			// #155
			$row["id"] = trim($row["id"]);
			$row["title"] = trim($row["title"]);
			
			$res[] = $row;
		}
		
		return $res;
	}
	
	
	//
	// helper
	//
	
	/**
	 * Check if given color is valid
	 * 
	 * @param string $a_color
	 * @return bool
	 */
	public static function isValidColor($a_color)
	{
		if(strlen($a_color) == 6)
		{
			$r = hexdec(substr($a_color, 0, 2));
			$g = hexdec(substr($a_color, 2, 2));
			$b = hexdec(substr($a_color, 4, 2));
			
			return ($r >= 0 && $r <= 255 &&
				$g >= 0 && $g <= 255 &&
				$b >= 0 && $b <= 255);
		}
		
		return false;
	}
	
	/**
	 * Get font color (black/white) for background color
	 * 
	 * @param string $a_bg_color
	 * @return string
	 */
	public static function getFontColorForBg($a_bg_color)
	{		
		if(self::isValidColor($a_bg_color))
		{
			// black or white font-color?
			// http://en.wikipedia.org/wiki/Luminance_(relative)
			$lum = round(hexdec(substr($a_bg_color, 0, 2))*0.2126+
				hexdec(substr($a_bg_color, 2, 2))*0.7152+
				hexdec(substr($a_bg_color, 4, 2))*0.0722);
			return ($lum <= 128) ? "FFFFFF" : "000000";
		}
	}
}
