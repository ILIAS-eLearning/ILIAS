<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * parses the template.xml that defines all styles of the current template
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 */
require_once("./Services/Xml/classes/class.ilSaxParser.php");
class ilStyleDefinition extends ilSaxParser
{
	/**
	 * currently selected skin
	 * @var string
	 */
	static $current_skin;
	
	
	/**
	 * currently selected style
	 * @var string
	 */
	static $current_style;
	
	static $current_master_style;


	/**
	* Constructor
	*
	* parse
	*
	* @access	public
	*/
	function ilStyleDefinition($a_template_id = "")
	{
		global $ilias;

		if ($a_template_id == "")
		{
			// use function to get the current skin
			$a_template_id = self::getCurrentSkin();
		}

		// remember the template id
		$this->template_id = $a_template_id;

		if ($a_template_id == "default")
		{
			parent::ilSaxParser("./templates/".$a_template_id."/template.xml");
		}
		else
		{
			parent::ilSaxParser("./Customizing/global/skin/".$a_template_id."/template.xml");
		}
	}


	// PUBLIC METHODS

	/**
	* get translation type (sys, db or 0)s
	*
	* @param	string	object type
	* @access	public
	*/
	function getStyles()
	{
//echo ":".count($this->styles).":";
		if (is_array($this->styles))
		{
			return $this->styles;
		}
		else
		{
			return array();
		}
	}

	function getTemplateId()
	{
		return $this->template_id;
	}

	
	function getTemplateName()
	{
		return $this->template_name;
	}


	function getStyle($a_id)
	{
		return $this->styles[$a_id];
	}


	function getStyleName($a_id)
	{
		return $this->styles[$a_id]["name"];
	}


	function getImageDirectory($a_master_style, $a_substyle = "")
	{
		if ($a_substyle != $a_master_style && $a_substyle != "")
		{
			return $this->styles[$a_master_style]["substyle"][$a_substyle]["image_directory"];
		}
		return $this->styles[$a_master_style]["image_directory"];
	}

	function getSoundDirectory($a_id)
	{
		return $this->styles[$a_id]["sound_directory"];
	}
	
	public static function _getAllTemplates()
	{
		$skins = array();

		$skins[] = array("id" => "default");
		if ($dp = @opendir("./Customizing/global/skin"))
		{
			while (($file = readdir($dp)) != false)
			{
				//is the file a directory?
				if (is_dir("./Customizing/global/skin/".$file) && $file != "." && $file != ".." && $file != "CVS"
					&& $file != ".svn")
				{
					if (is_file("./Customizing/global/skin/".$file."/template.xml"))
					{
						$skins[] = array(
							"id" => $file
						);
					}
				}
			} // while
		}
		else
		{
			return $skins;
		}

		return $skins;
		
	}

	function getAllTemplates()
	{
		return self::_getAllTemplates();
	}
	

	// PRIVATE METHODS

	/**
	* set event handler
	*
	* @param	ressouce	internal xml_parser_handler
	* @access	private
	*/
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
		xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
	}

	/**
	* start tag handler
	*
	* @param	ressouce	internal xml_parser_handler
	* @param	string		element tag name
	* @param	array		element attributes
	* @access	private
	*/
	function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
	{
		if (!isset($a_attribs["sound_directory"]))
		{
			$a_attribs["sound_directory"] = "";
		}
		
		if (!isset($a_attribs["browsers"]))
		{
			$a_attribs["browsers"] = "";
		}
		
		switch($a_name)
		{
			case "template" :
				$this->template_name = $a_attribs["name"];
				break;

			case "style" :
				$this->last_style_id = $a_attribs["id"];
				$this->styles[$a_attribs["id"]] =
					array(	"id" => $a_attribs["id"],
							"name" => $a_attribs["name"],
							"css_file" => $a_attribs["id"].".css",
							"image_directory" => $a_attribs["image_directory"],
							"sound_directory" => $a_attribs["sound_directory"]
					);
				$browsers =
					explode(",", $a_attribs["browsers"]);
				foreach ($browsers as $val)
				{
					$this->styles[$a_attribs["id"]]["browsers"][] = trim($val);
				}
				break;
				
			case "substyle":
				$this->styles[$this->last_style_id]["substyle"][$a_attribs["id"]] =
					array(	"id" => $a_attribs["id"],
							"name" => $a_attribs["name"],
							"css_file" => $a_attribs["id"].".css",
							"image_directory" => $a_attribs["image_directory"],
							"sound_directory" => $a_attribs["sound_directory"]
					);
				break;
		}
	}
	
	
	/**
	* Check wheter a style exists
	*
	* @param	string	$skin		skin id
	* @param	string	$style		style id
	*
	* @return	boolean
	*/
	static function styleExists($skin, $style)
	{
		if ($skin == "default")
		{		
			if (is_file("./templates/".$skin."/template.xml") &&
				is_file("./templates/".$skin."/".$style.".css")
				)
			{
				return true;
			}
		}
		else
		{
			if (is_file("./Customizing/global/skin/".$skin."/template.xml") &&
				is_file("./Customizing/global/skin/".$skin."/".$style.".css")
				)
			{
				return true;
			}
		}
		return false;
	}

	/**
	* Check wheter a skin exists
	*
	* @param	string	$skin		skin id
	*
	* @return	boolean
	*/
	static function skinExists($skin)
	{
		if ($skin == "default")
		{		
			if (is_file("./templates/".$skin."/template.xml"))
			{
				return true;
			}
		}
		else
		{
			if (is_file("./Customizing/global/skin/".$skin."/template.xml"))
			{
				return true;
			}
		}
		return false;
	}

	/**
	* end tag handler
	*
	* @param	ressouce	internal xml_parser_handler
	* @param	string		data
	* @access	private
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		// DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
		$a_data = preg_replace("/\n/","",$a_data);
		$a_data = preg_replace("/\t+/","",$a_data);

		if(!empty($a_data))
		{
			switch($this->current_tag)
			{
				default:
					break;
			}
		}
	}

	/**
	* end tag handler
	*
	* @param	ressouce	internal xml_parser_handler
	* @param	string		element tag name
	* @access	private
	*/
	function handlerEndTag($a_xml_parser,$a_name)
	{
	}
		
	
	/**
	 * get the current skin
	 *
	 * use always this function instead of getting the account's skin
	 * the current skin may be changed on the fly by setCurrentSkin()
	 * 
	 * @return	string|null	skin id
	 */
	public static function getCurrentSkin()
	{
		/**
		 * @var $ilias ILIAS
		 */
		global $ilias;
		
		if(isset(self::$current_skin))
		{
			return self::$current_skin;
		}

		if(is_object($ilias))
		{
			return $ilias->account->skin;
		}

		return null;
	}
	
	/**
	 * get the current style
	 *
	 * use always this function instead of getting the account's style
	 * the current style may be changed on the fly by setCurrentStyle()

	 * @return	string|null	style id
	 */
	public static function getCurrentStyle()
	{
		global $ilias, $tree, $styleDefinition, $tree;	
		
		if (isset(self::$current_style))
		{
			return self::$current_style;
		}

		if(!is_object($ilias))
		{
			return null;
		}

		$cs = $ilias->account->prefs['style'];
		
		if (is_object($styleDefinition))
		{
			// are there any substyles?
			$styles = $styleDefinition->getStyles();
			if (is_array($styles[$cs]["substyle"]))
			{
				// read assignments, if given
				$assignmnts = self::getSystemStyleCategoryAssignments(self::getCurrentSkin(), $cs);
				if (count($assignmnts) > 0)
				{
					$ref_ass = array();
					foreach ($assignmnts as $a)
					{
						$ref_ass[$a["ref_id"]] = $a["substyle"];
					}

					// check whether any ref id assigns a new style
					if (is_object($tree) && $_GET["ref_id"] > 0 &&
						$tree->isInTree($_GET["ref_id"]))
					{
						$path = $tree->getPathId((int) $_GET["ref_id"]);
						for ($i = count($path) - 1; $i >= 0; $i--)
						{
							if (isset($ref_ass[$path[$i]]))
							{
								self::$current_style = $ref_ass[$path[$i]];
								return self::$current_style;
							}
						}
					}
				}
			}
		}
		
		if ($_GET["ref_id"] != "")
		{
			self::$current_style = $cs;
		}
		
		return $cs;
	}
	
		/**
	 * get the current style
	 *
	 * use always this function instead of getting the account's style
	 * the current style may be changed on the fly by setCurrentStyle()

	 * @return	string	style id
	 */
	public static function getCurrentMasterStyle()
	{
		global $ilias;	
		
		if (isset(self::$current_master_style))
		{
			return self::$current_master_style;
		}

		$cs = $ilias->account->prefs['style'];

		self::$current_master_style = $cs;
		
		return $cs;
	}

	
	/**
	 * set a new current skin
	 * 
	 * @param	string		skin id
	 */
	public static function setCurrentSkin($a_skin)
	{
		global $styleDefinition;

		if (is_object($styleDefinition)
		and $styleDefinition->getTemplateId() != $a_skin)
		{
			$styleDefinition = new ilStyleDefinition($a_skin);
			$styleDefinition->startParsing();
		}
		
		self::$current_skin = $a_skin;
	}
	
	
	/**
	 * set a new current style
	 * 
	 * @param	string	style id
	 */
	public static function setCurrentStyle($a_style)
	{
		self::$current_style = $a_style;
	}
	
	/**
	 * Get all skins/styles
	 *
	 * @param
	 * @return
	 */
	public static function getAllSkinStyles()
	{
		global $styleDefinition;
		
		$all_styles = array();
		
		$templates = $styleDefinition->getAllTemplates();
		
		foreach ($templates as $template)
		{
			// get styles definition for template
			$styleDef = new ilStyleDefinition($template["id"]);
			$styleDef->startParsing();
			$styles = $styleDef->getStyles();

			foreach ($styles as $style)
			{
				$num_users = ilObjUser::_getNumberOfUsersForStyle($template["id"], $style["id"]);
				
				// default selection list
				$all_styles[$template["id"].":".$style["id"]] =
					array (
						"title" => $styleDef->getTemplateName()." / ".$style["name"],
						"id" => $template["id"].":".$style["id"],
						"template_id" => $template["id"],
						"style_id" => $style["id"],
						"template_name" => $styleDef->getTemplateName(),
						"substyle" => $style["substyle"],
						"style_name" => $style["name"],
						"users" => $num_users
						);
			}
		}

		return $all_styles;
	}
	
	/**
	 * Get all system style category assignments
	 *
	 * @param string $a_skin_id skin id
	 * @param string $a_style_id style id
	 * @return array ref ids
	 */
	static function getSystemStyleCategoryAssignments($a_skin_id, $a_style_id)
	{
		global $ilDB;
		
		$assignmnts = array();
		$set = $ilDB->query("SELECT substyle, category_ref_id FROM syst_style_cat ".
			" WHERE skin_id = ".$ilDB->quote($a_skin_id, "text").
			" AND style_id = ".$ilDB->quote($a_style_id, "text")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$assignmnts[] = array("substyle" => $rec["substyle"],
				"ref_id" => $rec["category_ref_id"]);
		}
		return $assignmnts;
	}
	
	/**
	 * Write category assignment
	 *
	 * @param
	 * @return
	 */
	function writeSystemStyleCategoryAssignment($a_skin_id, $a_style_id,
		$a_substyle, $a_ref_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("INSERT INTO syst_style_cat ".
			"(skin_id, style_id, substyle, category_ref_id) VALUES (".
			$ilDB->quote($a_skin_id, "text").",".
			$ilDB->quote($a_style_id, "text").",".
			$ilDB->quote($a_substyle, "text").",".
			$ilDB->quote($a_ref_id, "integer").
			")");
	}
	
	/**
	 * Delete category style assignment
	 *
	 * @param
	 * @return
	 */
	function deleteSystemStyleCategoryAssignment($a_skin_id, $a_style_id,
		$a_substyle, $a_ref_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM syst_style_cat WHERE ".
			" skin_id = ".$ilDB->quote($a_skin_id, "text").
			" AND style_id = ".$ilDB->quote($a_style_id, "text").
			" AND substyle = ".$ilDB->quote($a_substyle, "text").
			" AND category_ref_id = ".$ilDB->quote($a_ref_id, "integer"));
	}
	
}
?>
