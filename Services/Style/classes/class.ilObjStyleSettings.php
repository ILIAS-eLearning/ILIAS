<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjStyleSettings
* 
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
* @extends ilObject
*/

require_once "./Services/Object/classes/class.ilObject.php";

class ilObjStyleSettings extends ilObject
{
	var $styles;
	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function __construct($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "stys";
		parent::__construct($a_id,$a_call_by_reference);
		
		$this->styles = array();
	}
	
	/**
	* add style to style folder
	*
	* @param	int		$a_style_id		style id
	*/
	function addStyle($a_style_id)
	{
		$this->styles[$a_style_id] =
			array("id" => $a_style_id,
			"title" => ilObject::_lookupTitle($a_style_id));
	}

	
	/**
	* remove Style from style list
	*/
	function removeStyle($a_id)
	{
		unset($a_id);
	}


	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		global $ilDB;
		
		if (!parent::update())
		{			
			return false;
		}

		// save styles of style folder
		$q = "DELETE FROM style_folder_styles WHERE folder_id = ".
			$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($q);
		foreach($this->styles as $style)
		{
			$q = "INSERT INTO style_folder_styles (folder_id, style_id) VALUES".
				"(".$ilDB->quote((int) $this->getId(), "integer").", ".
				$ilDB->quote((int) $style["id"], "integer").")";
			$ilDB->manipulate($q);
		}
		
		return true;
	}
	
	/**
	* read style folder data
	*/
	function read()
	{
		global $ilDB;

		parent::read();

		// get styles of style folder
		$q = "SELECT * FROM style_folder_styles, style_data WHERE folder_id = ".
			$ilDB->quote($this->getId(), "integer").
			" AND style_id = style_data.id";

		$style_set = $ilDB->query($q);
		$this->styles = array();
		while ($style_rec = $ilDB->fetchAssoc($style_set))
		{
			$this->styles[$style_rec["style_id"]] =
				array("id" => $style_rec["style_id"],
				"title" => ilObject::_lookupTitle($style_rec["style_id"]),
				"category" => $style_rec["category"]);
		}
		$this->styles =
			ilUtil::sortArray($this->styles, "title", "asc", false, true);
	}
	
	/**
	* lookup if a style is activated
	*/
	static function _lookupActivatedStyle($a_skin, $a_style)
	{
		global $ilDB;
		
		$q = "SELECT count(*) cnt FROM settings_deactivated_s".
			" WHERE skin = ".$ilDB->quote($a_skin, "text").
			" AND style = ".$ilDB->quote($a_style, "text")." ";
		
		$cnt_set = $ilDB->query($q);
		$cnt_rec = $ilDB->fetchAssoc($cnt_set);
		
		if ($cnt_rec["cnt"] > 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	* deactivate style
	*/
	static function _deactivateStyle($a_skin, $a_style)
	{
		global $ilDB;

		ilObjStyleSettings::_activateStyle($a_skin, $a_style);
		$q = "INSERT into settings_deactivated_s".
			" (skin, style) VALUES ".
			" (".$ilDB->quote($a_skin, "text").",".
			" ".$ilDB->quote($a_style, "text").")";

		$ilDB->manipulate($q);
	}

	/**
	* activate style
	*/
	static function _activateStyle($a_skin, $a_style)
	{
		global $ilDB;

		$q = "DELETE FROM settings_deactivated_s".
			" WHERE skin = ".$ilDB->quote($a_skin, "text").
			" AND style = ".$ilDB->quote($a_style, "text");

		$ilDB->manipulate($q);
	}
	
	/**
	* get style ids
	*
	* @return		array		ids
	*/
	function getStyles()
	{
		return $this->styles;
	}
	

	/**
	* delete object and all related data	
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{		
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}
		
		//put here your module specific stuff
		
		return true;
	}

} // END class.ilObjStyleSettings
?>
