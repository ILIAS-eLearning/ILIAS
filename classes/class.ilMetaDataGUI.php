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

	/**
	* shows language select box
	*/
	function showLangSel($a_name, $a_value = "")
	{
		$tpl = new ilTemplate("tpl.lang_selection.html", true, true);
		$languages = ilMetaData::getLanguages();
		foreach($languages as $code => $text)
		{
			$tpl->setCurrentBlock("lg_option");
			$tpl->setVariable("VAL_LG", $code);
			$tpl->setVariable("TXT_LG", $text);
			if ($a_value != "" &&
				$a_value == $code)
			{
				$tpl->setVariable("SELECTED", "selected");
			}
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
		$tpl->setVariable("SEL_NAME", $a_name);
		$return = $tpl->get();
		unset($tpl);

		return $return;
	}

	function fillGeneral($a_formaction, $a_section = "General", $a_language = "")
	{
		$tpl = new ilTemplate("tpl.meta_data_editor_general.html", true, true);
		$tpl->setVariable("TXT_GENERAL", $this->lng->txt("meta_general"));
		$tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
		$tpl->setVariable("TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
		$tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
		$tpl->setVariable("TXT_KEYWORD", $this->lng->txt("meta_keyword"));
		$tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("meta_description"));
		$tpl->setVariable("TXT_ADD", $this->lng->txt("meta_add"));
		$tpl->setVariable("TXT_STRUCTURE", $this->lng->txt("meta_structure"));
		$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
		$tpl->setVariable("TXT_ATOMIC", $this->lng->txt("meta_atomic"));
		$tpl->setVariable("TXT_COLLECTION", $this->lng->txt("meta_collection"));
		$tpl->setVariable("TXT_NETWORKED", $this->lng->txt("meta_networked"));
		$tpl->setVariable("TXT_HIERARCHICAL", $this->lng->txt("meta_hierarchical"));
		$tpl->setVariable("TXT_LINEAR", $this->lng->txt("meta_linear"));

		if (is_array($general = $this->meta_obj->getElement("General")))
		{
			$tpl->setVariable("STRUCTURE_VAL_" . strtoupper($general[0]["Structure"]), " selected");
		}

		/* Identifier */
		if (is_array($identifier = $this->meta_obj->getElement("Identifier", "General")))
		{
			for ($i = 0; $i < count($identifier); $i++)
			{
				$tpl->setCurrentBlock("identifier_loop");
				$tpl->setVariable("IDENTIFIER_LOOP_TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
				$tpl->setVariable("IDENTIFIER_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
				$tpl->setVariable("IDENTIFIER_LOOP_VAL_IDENTIFIER", $identifier[$i]["value"]);
				$tpl->setVariable("IDENTIFIER_LOOP_TXT_CATALOG", $this->lng->txt("meta_catalog"));
				$tpl->setVariable("IDENTIFIER_LOOP_VAL_IDENTIFIER_CATALOG", $identifier[$i]["Catalog"]);
				$tpl->setVariable("IDENTIFIER_LOOP_TXT_ENTRY", $this->lng->txt("meta_entry"));
				$tpl->setVariable("IDENTIFIER_LOOP_VAL_IDENTIFIER_ENTRY", $identifier[$i]["Entry"]);
				$tpl->setVariable("IDENTIFIER_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=General&meta_name=Identifier&meta_index=" . $i);
				$tpl->setVariable("IDENTIFIER_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
				$tpl->setVariable("IDENTIFIER_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Identifier&meta_language=" . $a_language . "&meta_path=General&meta_section=" . $a_section);
				$tpl->setVariable("IDENTIFIER_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
				$tpl->parseCurrentBlock();
			}
		}

		/* Language */
		if (is_array($language = $this->meta_obj->getElement("Language", "General")))
		{
			for ($i = 0; $i < count($language); $i++)
			{
				$tpl->setCurrentBlock("language_loop");
				$tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$tpl->setVariable("LANGUAGE_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
				$tpl->setVariable("LANGUAGE_LOOP_VAL", $this->showLangSel("meta[language_value][]", $language[$i]["value"]));
				$tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$tpl->setVariable("LANGUAGE_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[language_language][]", $language[$i]["Language"]));

				$tpl->setVariable("LANGUAGE_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=General&meta_name=Language&meta_index=" . $i);
				$tpl->setVariable("LANGUAGE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
				$tpl->setVariable("LANGUAGE_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Language&meta_language=" . $a_language . "&meta_path=General&meta_section=" . $a_section);
				$tpl->setVariable("LANGUAGE_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
				$tpl->parseCurrentBlock();
			}
		}

		/* Title */
		$title = $this->meta_obj->getElement("Title", "General");
		$tpl->setVariable("TXT_TITLE", $this->lng->txt("meta_title"));
		$tpl->setVariable("TXT_VALUE", $this->lng->txt("meta_value"));
		$tpl->setVariable("VAL_TITLE", $title[0]["value"]);
		$tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
		$tpl->setVariable("VAL_TITLE_LANGUAGE", $this->showLangSel("meta[title_language]", $title[0]["Language"]));

		/* Description */
		if (is_array($description = $this->meta_obj->getElement("Description", "General")))
		{
			for ($i = 0; $i < count($description); $i++)
			{
				$tpl->setCurrentBlock("description_loop");
				$tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
				$tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
				$tpl->setVariable("DESCRIPTION_LOOP_VAL", $description[$i]["value"]);
				$tpl->setVariable("DESCRIPTION_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$tpl->setVariable("DESCRIPTION_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[description_language][]", $description[$i]["Language"]));
				$tpl->setVariable("DESCRIPTION_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=General&meta_name=Description&meta_index=" . $i);
				$tpl->setVariable("DESCRIPTION_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
				$tpl->setVariable("DESCRIPTION_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Description&meta_language=" . $a_language . "&meta_path=General&meta_section=" . $a_section);
				$tpl->setVariable("DESCRIPTION_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
				$tpl->parseCurrentBlock();
			}
		}

		/* Keyword */
		if (is_array($keyword = $this->meta_obj->getElement("Keyword", "General")))
		{
			for ($i = 0; $i < count($keyword); $i++)
			{
				$tpl->setCurrentBlock("keyword_loop");
				$tpl->setVariable("KEYWORD_LOOP_TXT_KEYWORD", $this->lng->txt("meta_keyword"));
				$tpl->setVariable("KEYWORD_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
				$tpl->setVariable("KEYWORD_LOOP_VAL", $keyword[$i]["value"]);
				$tpl->setVariable("KEYWORD_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$tpl->setVariable("KEYWORD_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[keyword_language][]", $keyword[$i]["Language"]));
				$tpl->setVariable("KEYWORD_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=General&meta_name=Keyword&meta_index=" . $i);
				$tpl->setVariable("KEYWORD_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
				$tpl->setVariable("KEYWORD_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Keyword&meta_language=" . $a_language . "&meta_path=General&meta_section=" . $a_section);
				$tpl->setVariable("KEYWORD_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
				$tpl->parseCurrentBlock();
			}
		}

		$tpl->setVariable("EDIT_ACTION", $a_formaction . "&cmd=post");
		$tpl->setVariable("VAL_SECTION", $a_section);
		$tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

		$this->tpl->setCurrentBlock("general");
		$this->tpl->setVariable("GENERAL", $tpl->get());
		$this->tpl->parseCurrentBlock();
		unset($tpl);
		
		return true;
	}

	function edit($a_temp_var, $a_temp_block, $a_formaction, $a_section = "General", $a_language = "")
	{
		if ($a_language == "")
		{
			$a_language = $this->ilias->account->getLanguage();
		}
		//$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.meta_data.html", true);
		$this->tpl->addBlockFile($a_temp_var, $a_temp_block, "tpl.meta_data_editor.html", false);
		//$this->tpl->setVariable("FORMACTION", "lm_edit.php?lm_id=".
		//	$this->lm_obj->getId()."&obj_id=".$this->meta_obj->getId()."&cmd=save_meta");

		/* General */
		if ($a_section == "General")
		{
			$this->fillGeneral($a_formaction, $a_section, $a_language);
		}

		/* Relation */
		if ($a_section == "Relation")
		{
			/* Relation */
			if (!is_array($relation = $this->meta_obj->getElement("Relation")))
			{
				$this->tpl->setCurrentBlock("no_relation");
				$this->tpl->setVariable("TXT_NO_RELATION", $this->lng->txt("meta_no_relation"));
				$this->tpl->setVariable("TXT_ADD_RELATION", $this->lng->txt("meta_add"));
				$this->tpl->setVariable("ACTION_ADD_RELATION", $a_formaction . "&cmd=addMeta&meta_name=Relation&meta_language=" . $a_language . "&meta_section=" . $a_section);
				$this->tpl->parseCurrentBlock();
			}
			else
			{

			$this->tpl->setVariable("RELATION_TXT_RELATION", $this->lng->txt("meta_relation"));
			$this->tpl->setVariable("RELATION_TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
			$this->tpl->setVariable("RELATION_TXT_ELEMENT_IDENTIFIER", $this->lng->txt("meta_identifier"));
			$this->tpl->setVariable("RELATION_TXT_ELEMENT_DESCRIPTION", $this->lng->txt("meta_description"));
			$this->tpl->setVariable("RELATION_TXT_ADD", $this->lng->txt("meta_add"));
			$this->tpl->setVariable("RELATION_TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
			$this->tpl->setVariable("RELATION_TXT_KIND", $this->lng->txt("meta_kind"));
			$this->tpl->setVariable("RELATION_TXT_KIND_ISPARTOF", $this->lng->txt("meta_ispartof"));
			$this->tpl->setVariable("RELATION_TXT_KIND_HASPART", $this->lng->txt("meta_haspart"));
			$this->tpl->setVariable("RELATION_TXT_KIND_ISVERSIONOF", $this->lng->txt("meta_isversionof"));
			$this->tpl->setVariable("RELATION_TXT_KIND_HASVERSION", $this->lng->txt("meta_hasversion"));
			$this->tpl->setVariable("RELATION_TXT_KIND_ISFORMATOF", $this->lng->txt("meta_isformatof"));
			$this->tpl->setVariable("RELATION_TXT_KIND_HASFORMAT", $this->lng->txt("meta_hasformat"));
			$this->tpl->setVariable("RELATION_TXT_KIND_REFERENCES", $this->lng->txt("meta_references"));
			$this->tpl->setVariable("RELATION_TXT_KIND_ISREFERENCEDBY", $this->lng->txt("meta_isreferencedby"));
			$this->tpl->setVariable("RELATION_TXT_KIND_ISBASEDON", $this->lng->txt("meta_isbasedon"));
			$this->tpl->setVariable("RELATION_TXT_KIND_ISBASISFOR", $this->lng->txt("meta_isbasisfor"));
			$this->tpl->setVariable("RELATION_TXT_KIND_REQUIRES", $this->lng->txt("meta_requires"));
			$this->tpl->setVariable("RELATION_TXT_KIND_ISREQUIREDBY", $this->lng->txt("meta_isrequiredby"));
			$this->tpl->setVariable("RELATION_VAL_KIND_" . strtoupper($general[0]["Kind"]), " selected");

/*			$resource = false;
			if (is_array($identifier = $this->meta_obj->getElement("Identifier_", "Relation/Resource")))
			{
				$resource = true;
				for ($i = 0; $i < count($identifier); $i++)
				{
					$this->tpl->setCurrentBlock("relation_resource_loop");
					$this->tpl->setVariable("RELATION_RESOURCE_IDENTIFIER_LOOP_TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
					$this->tpl->setVariable("RELATION_RESOURCE_IDENTIFIER_LOOP_VAL", $identifier[$i]["value"]);
					$this->tpl->setVariable("RELATION_RESOURCE_IDENTIFIER_LOOP_TXT_CATALOG", $this->lng->txt("meta_catalog"));
					$this->tpl->setVariable("RELATION_RESOURCE_IDENTIFIER_LOOP_VAL_CATALOG", $identifier[$i]["Catalog"]);
					$this->tpl->setVariable("RELATION_RESOURCE_IDENTIFIER_LOOP_TXT_ENTRY", $this->lng->txt("meta_entry"));
					$this->tpl->setVariable("RELATION_RESOURCE_IDENTIFIER_LOOP_VAL_ENTRY", $identifier[$i]["Entry"]);
					$this->tpl->setVariable("RELATION_RESOURCE_IDENTIFIER_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=General&meta_name=Identifier&meta_index=" . $i);
					$this->tpl->setVariable("RELATION_RESOURCE_IDENTIFIER_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
					$this->tpl->parseCurrentBlock();
				}
			}*/

			/* Description */
/*			if (is_array($description = $this->meta_obj->getElement("Description", "General")))
			{
				$resource = true;
				for ($i = 0; $i < count($description); $i++)
				{
					$this->tpl->setCurrentBlock("description_loop");
					$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
					$this->tpl->setVariable("DESCRIPTION_LOOP_VAL", $description[$i]["value"]);
					$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
					$this->tpl->setVariable("DESCRIPTION_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[description_language][]", $description[$i]["Language"]));
					$this->tpl->setVariable("DESCRIPTION_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=General&meta_name=Description&meta_index=" . $i);
					$this->tpl->setVariable("DESCRIPTION_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
					$this->tpl->parseCurrentBlock();
				}
			}*/

				$this->tpl->setCurrentBlock("relation");
				$this->tpl->parseCurrentBlock();
			}

		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("CHOOSE_SECTION_ACTION", $a_formaction . "&cmd=chooseMetaSection");
		$this->tpl->setVariable("TXT_CHOOSE_SECTION", $this->lng->txt("meta_choose_section"));
		$this->tpl->setVariable("META_SECTION_" . strtoupper($a_section), " selected");
		$this->tpl->setVariable("TXT_OK", $this->lng->txt("ok"));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->parseCurrentBlock();
	}

	function save($a_section = "General")
	{
#		var_dump("<pre>",$_POST,"</pre>");
		$meta = $_POST["meta"] ? $_POST["meta"] : $_POST["Fobject"];
#		echo "Meta-Title: " . $meta["title"] . "<br>\n";
#		var_dump("<pre>",$meta,"</pre>");
		$this->meta_obj->setMeta($meta);
		$this->meta_obj->setSection($a_section);
		$this->meta_obj->setTitle($meta["title"]);
#		echo "Section: " . $section . "<br>\n";
		$this->obj->updateMetaData();
	}

	function &create()
	{
		$meta = $_POST["meta"] ? $_POST["meta"] : $_POST["Fobject"];
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
