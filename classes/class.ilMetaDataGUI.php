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

	function edit($a_temp_var, $a_temp_block, $a_formaction, $a_section = "general", $a_language = "")
	{
		if ($a_language == "")
		{
			$a_language = "de";
			/* ToDo: Ist $a_language == "", Systemsprache des Benutzers ermitteln und als Default-Wert benutzen. */
		}
		//$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.meta_data.html", true);
		$this->tpl->addBlockFile($a_temp_var, $a_temp_block, "tpl.meta_data_editor.html", false);
		//$this->tpl->setVariable("FORMACTION", "lm_edit.php?lm_id=".
		//	$this->lm_obj->getId()."&obj_id=".$this->meta_obj->getId()."&cmd=save_meta");

		$this->tpl->setVariable("CHOOSE_SECTION_ACTION", $a_formaction . "&cmd=choose_meta_section");
		$this->tpl->setVariable("TXT_CHOOSE_SECTION", $this->lng->txt("meta_choose_section"));
		$this->tpl->setVariable("META_SECTION_" . strtoupper($a_section), " selected");
		$this->tpl->setVariable("TXT_CHOOSE_LANGUAGE", $this->lng->txt("meta_choose_language"));
		$tpl = new ilTemplate("tpl.lang_selection.html", true, true);
		// $this->tpl->addBlockFile("SEL_META_LANGUAGE", "sel_meta_language", "tpl.lang_selection.html", false);
		$languages = ilMetaData::getLanguages();
		foreach($languages as $code => $language)
		{
			$tpl->setCurrentBlock("lg_option");
			$tpl->setVariable("VAL_LG", $code);
			$tpl->setVariable("TXT_LG", $language);
			if ($a_language == $code)
			{
				$tpl->setVariable("SELECTED", "selected");
			}
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
		$tpl->setVariable("SEL_NAME", "meta_language");
		$this->tpl->setVariable("SEL_META_LANGUAGE", $tpl->get());
		$this->tpl->setVariable("TXT_OK", $this->lng->txt("ok"));

		$this->tpl->setVariable("EDIT_ACTION", $a_formaction . "&cmd=save_meta");
		$this->tpl->setVariable("VAL_SECTION", $a_section);
		$this->tpl->setVariable("VAL_LANGUAGE", $a_language);
		if ($a_section == "general")
		{
			$this->tpl->setVariable("GENERAL_TXT_GENERAL", $this->lng->txt("meta_general"));
			$this->tpl->setVariable("GENERAL_TXT_STRUCTURE", $this->lng->txt("meta_structure"));
			$this->tpl->setVariable("GENERAL_TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
			$this->tpl->setVariable("GENERAL_TXT_STRUCTURE_ATOMIC", $this->lng->txt("meta_atomic"));
			$this->tpl->setVariable("GENERAL_TXT_STRUCTURE_COLLECTION", $this->lng->txt("meta_collection"));
			$this->tpl->setVariable("GENERAL_TXT_STRUCTURE_NETWORKED", $this->lng->txt("meta_networked"));
			$this->tpl->setVariable("GENERAL_TXT_STRUCTURE_HIERARCHICAL", $this->lng->txt("meta_hierarchical"));
			$this->tpl->setVariable("GENERAL_TXT_STRUCTURE_LINEAR", $this->lng->txt("meta_linear"));
			if (is_array($general = $this->meta_obj->getElement("General")))
			{
				$this->tpl->setVariable("GENERAL_VAL_STRUCTURE_" . strtoupper($general[0]["Structure"]), " selected");
			}
			if (is_array($identifier = $this->meta_obj->getElement("Identifier", "General")))
			{
				for ($i = 0; $i < count($identifier); $i++)
				{
					$this->tpl->setCurrentBlock("identifier_loop");
					$this->tpl->setVariable("IDENTIFIER_LOOP_TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
					$this->tpl->setVariable("IDENTIFIER_LOOP_VAL_IDENTIFIER", $identifier[$i]["value"]);
					$this->tpl->setVariable("IDENTIFIER_LOOP_TXT_CATALOG", $this->lng->txt("meta_catalog"));
					$this->tpl->setVariable("IDENTIFIER_LOOP_VAL_CATALOG", $identifier[$i]["Catalog"]);
					$this->tpl->setVariable("IDENTIFIER_LOOP_TXT_ENTRY", $this->lng->txt("meta_entry"));
					$this->tpl->setVariable("IDENTIFIER_LOOP_VAL_ENTRY", $identifier[$i]["Entry"]);
					$this->tpl->setVariable("IDENTIFIER_LOOP_ACTION_DELETE", $a_formaction . "&cmd=delete_meta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=General&meta_name=Identifier&meta_index=" . $i);
					$this->tpl->setVariable("IDENTIFIER_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
					$this->tpl->setVariable("IDENTIFIER_LOOP_ACTION_ADD", $a_formaction . "&cmd=add_meta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=General&meta_name=Identifier");
					$this->tpl->setVariable("IDENTIFIER_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$this->tpl->parseCurrentBlock();
				}
			}
			$this->tpl->setVariable("GENERAL_TXT_TITLE", $this->lng->txt("meta_title"));
			$this->tpl->setVariable("GENERAL_VAL_TITLE", $this->curValue("Title"));
			$this->tpl->setVariable("GENERAL_TXT_LANGUAGE", $this->lng->txt("meta_language"));
			$tpl = new ilTemplate("tpl.lang_selection.html", true, true);
//			$this->tpl->addBlockFile("GENERAL_SEL_LANGUAGE", "sel_language", "tpl.lang_selection.html", false);
			$languages = ilMetaData::getLanguages();
			foreach($languages as $code => $language)
			{
				$tpl->setCurrentBlock("lg_option");
				$tpl->setVariable("VAL_LG", $code);
				$tpl->setVariable("TXT_LG", $language);
				$tpl->parseCurrentBlock();
			}
			$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
			$tpl->setVariable("SEL_NAME", "language");
			$this->tpl->setVariable("GENERAL_SEL_LANGUAGE", $tpl->get());
			$this->tpl->setCurrentBlock("general");
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
