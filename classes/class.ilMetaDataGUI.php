<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once("./classes/class.ilMetaData.php");

/**
* Class ilMetaDataGUI
*
* GUI class for ilMetaData
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilMetaDataGUI
{
	var $ilias;
	var $tpl;
	var $lng;
	var $obj;
	var $meta_obj;


	/**
	* Constructor
	* @access	public
	*/
	function ilMetaDataGUI()
	{
		global $ilias, $tpl, $lng;
		$lng->LoadLanguageModule("meta");

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;

	}


	function setObject($a_obj)
	{
		$this->obj =& $a_obj;
		$this->meta_obj =& $this->obj->getMetaData();
	}

	/**
	* use this method to initialize form fields
	*/
	function curValue($a_val_name)
	{
		if(is_object($this->meta_obj))
		{
			$method = "get".$a_val_name;
			return $this->meta_obj->$method();
		}
		else
		{
			return "";
		}
	}

	function edit($a_temp_var, $a_temp_block, $a_formaction, $a_section = "general")
	{
		//$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.meta_data.html", true);
		$this->tpl->addBlockFile($a_temp_var, $a_temp_block, "tpl.meta_data_editor.html", false);
		//$this->tpl->setVariable("FORMACTION", "lm_edit.php?lm_id=".
		//	$this->lm_obj->getId()."&obj_id=".$this->meta_obj->getId()."&cmd=save_meta");
		$this->tpl->setVariable("CHOOSE_SECTION_ACTION", $a_formaction . "&cmd=choose_meta_section");
		$this->tpl->setVariable("TXT_CHOOSE_SECTION", $this->lng->txt("meta_choose_section"));
		$this->tpl->setVariable("META_SECTION_" . strtoupper($_POST["meta_section"]), " selected");
		$this->tpl->setVariable("TXT_OK", $this->lng->txt("ok"));
		$this->tpl->setVariable("EDIT_ACTION", $a_formaction . "&cmd=save_meta");
		$this->tpl->setVariable("VAL_SECTION", $_POST["meta_section"]);
		$this->tpl->setVariable("TXT_GENERAL", $this->lng->txt("meta_general"));
		$this->tpl->setVariable("TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
		$this->tpl->setVariable("VAL_IDENTIFIER", $this->curValue("ID"));
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("meta_title"));
		$this->tpl->setVariable("VAL_TITLE", $this->curValue("Title"));
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
		$this->tpl->addBlockFile("SEL_LANGUAGE", "sel_language", "tpl.lang_selection.html", false);
		$this->tpl->setVariable("SEL_NAME", "language");
		$languages = ilMetaData::getLanguages();
		foreach($languages as $code => $language)
		{
			$this->tpl->setCurrentBlock("lg_option");
			$this->tpl->setVariable("VAL_LG", $code);
			$this->tpl->setVariable("TXT_LG", $language);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();
	}

	function save()
	{
		$meta = $_POST["meta"];
		$this->meta_obj->setTitle($meta["title"]);
		$this->obj->updateMetaData();
	}

	function &create()
	{
		$meta = $_POST["meta"];
		$this->meta_obj =& new ilMetaData();
		$this->meta_obj->setTitle($meta["title"]);

		return $this->meta_obj;
	}

	/**
	* get target frame for command (command is method name without "Object", e.g. "perm")
	* @param	string		$a_cmd			command
	* @param	string		$a_target_frame	default target frame (is returned, if no special
	*										target frame was set)
	* @access	public 
	*/
	function getTargetFrame($a_cmd, $a_target_frame = "")
	{
		if ($this->target_frame[$a_cmd] != "")
		{
			return $this->target_frame[$a_cmd];
		}
		elseif (!empty($a_target_frame))
		{
			return $a_target_frame;
		}
		else
		{
			return;
		}
	}

	/**
	* set specific target frame for command
	* @param	string		$a_cmd			command
	* @param	string		$a_target_frame	default target frame (is returned, if no special
	*										target frame was set)
	* @access	public 
	*/
	function setTargetFrame($a_cmd, $a_target_frame)
	{
		$this->target_frame[$a_cmd] = $a_target_frame;
	}
}
?>
