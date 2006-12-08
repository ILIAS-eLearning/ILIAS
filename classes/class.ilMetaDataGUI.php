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


	function setObject(&$a_obj)
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
				if (count($identifier) > 1)
				{
					$tpl->setCurrentBlock("identifier_delete");
					$tpl->setVariable("IDENTIFIER_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=General&meta_name=Identifier&meta_index=" . $i);
					$tpl->setVariable("IDENTIFIER_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("identifier_loop");
				$tpl->setVariable("IDENTIFIER_LOOP_NO", $i);
				$tpl->setVariable("IDENTIFIER_LOOP_TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
				$tpl->setVariable("IDENTIFIER_LOOP_TXT_CATALOG", $this->lng->txt("meta_catalog"));
				$tpl->setVariable("IDENTIFIER_LOOP_VAL_IDENTIFIER_CATALOG", ilUtil::prepareFormOutput($identifier[$i]["Catalog"]));
				$tpl->setVariable("IDENTIFIER_LOOP_TXT_ENTRY", $this->lng->txt("meta_entry"));
				$tpl->setVariable("IDENTIFIER_LOOP_VAL_IDENTIFIER_ENTRY", ilUtil::prepareFormOutput($identifier[$i]["Entry"]));
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
				if (count($language) > 1)
				{
					$tpl->setCurrentBlock("language_delete");
					$tpl->setVariable("LANGUAGE_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=General&meta_name=Language&meta_index=" . $i);
					$tpl->setVariable("LANGUAGE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("language_loop");
				$tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$tpl->setVariable("LANGUAGE_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[Language][" . $i . "][Language]", $language[$i]["Language"]));
				$tpl->setVariable("LANGUAGE_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Language&meta_language=" . $a_language . "&meta_path=General&meta_section=" . $a_section);
				$tpl->setVariable("LANGUAGE_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
				$tpl->parseCurrentBlock();
			}
		}

		/* Title */
		$title = $this->meta_obj->getElement("Title", "General");
		$tpl->setVariable("TXT_TITLE", $this->lng->txt("meta_title"));
		$tpl->setVariable("TXT_VALUE", $this->lng->txt("meta_value"));
		$tpl->setVariable("VAL_TITLE", ilUtil::prepareFormOutput($title[0]["value"]));
		$tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
		$tpl->setVariable("VAL_TITLE_LANGUAGE", $this->showLangSel("meta[Title][Language]", $title[0]["Language"]));

		/* Description */
		if (is_array($description = $this->meta_obj->getElement("Description", "General")))
		{
			for ($i = 0; $i < count($description); $i++)
			{
				if (count($description) > 1)
				{
					$tpl->setCurrentBlock("description_delete");
					$tpl->setVariable("DESCRIPTION_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=General&meta_name=Description&meta_index=" . $i);
					$tpl->setVariable("DESCRIPTION_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("description_loop");
				$tpl->setVariable("DESCRIPTION_LOOP_NO", $i);
				$tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
				$tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
				$tpl->setVariable("DESCRIPTION_LOOP_VAL", ilUtil::stripSlashes($description[$i]["value"]));
				$tpl->setVariable("DESCRIPTION_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$tpl->setVariable("DESCRIPTION_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[Description][" . $i . "][Language]", $description[$i]["Language"]));
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
				if (count($keyword) > 1)
				{
					$tpl->setCurrentBlock("keyword_delete");
					$tpl->setVariable("KEYWORD_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=General&meta_name=Keyword&meta_index=" . $i);
					$tpl->setVariable("KEYWORD_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("keyword_loop");
				$tpl->setVariable("KEYWORD_LOOP_NO", $i);
				$tpl->setVariable("KEYWORD_LOOP_TXT_KEYWORD", $this->lng->txt("meta_keyword"));
				$tpl->setVariable("KEYWORD_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
				$tpl->setVariable("KEYWORD_LOOP_VAL", ilUtil::prepareFormOutput($keyword[$i]["value"]));
				$tpl->setVariable("KEYWORD_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$tpl->setVariable("KEYWORD_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[Keyword][" . $i . "][Language]", $keyword[$i]["Language"]));
				$tpl->setVariable("KEYWORD_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Keyword&meta_language=" . $a_language . "&meta_path=General&meta_section=" . $a_section);
				$tpl->setVariable("KEYWORD_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
				$tpl->parseCurrentBlock();
			}
		}

		/* Coverage */
		if (is_array($coverage = $this->meta_obj->getElement("Coverage", "General")))
		{
			$tpl->setCurrentBlock("keyword_loop");
			$tpl->setVariable("COVERAGE_LOOP_TXT_COVERAGE", $this->lng->txt("meta_coverage"));
			$tpl->setVariable("COVERAGE_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
			$tpl->setVariable("COVERAGE_LOOP_VAL", ilUtil::prepareFormOutput($coverage[0]["value"]));
			$tpl->setVariable("COVERAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
			$tpl->setVariable("COVERAGE_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[Coverage][Language]", $coverage[0]["Language"]));
			$tpl->setVariable("COVERAGE_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=General&meta_name=Coverage&meta_index=0");
			$tpl->setVariable("COVERAGE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->setVariable("TXT_COVERAGE", $this->lng->txt("meta_coverage"));
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

	function fillLifecycle($a_formaction, $a_section = "Lifecycle", $a_language = "")
	{
		if (!is_array($lifecycle = $this->meta_obj->getElement("Lifecycle")))
		{
			$this->tpl->setCurrentBlock("no_lifecycle");
			$this->tpl->setVariable("TXT_NO_LIFECYCLE", $this->lng->txt("meta_no_lifecycle"));
			$this->tpl->setVariable("TXT_ADD_LIFECYCLE", $this->lng->txt("meta_add"));
			$this->tpl->setVariable("ACTION_ADD_LIFECYCLE", $a_formaction . "&cmd=addMeta&meta_name=Lifecycle&meta_language=" . $a_language . "&meta_section=" . $a_section);
			$this->tpl->parseCurrentBlock();
		}
		else
		{

			$tpl = new ilTemplate("tpl.meta_data_editor_lifecycle.html", true, true);
			$tpl->setVariable("TXT_LIFECYCLE", $this->lng->txt("meta_lifecycle"));
			$tpl->setVariable("ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_name=Lifecycle&meta_index=0");
			$tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));
			$tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
			$tpl->setVariable("TXT_CONTRIBUTE", $this->lng->txt("meta_contribute"));
			$tpl->setVariable("TXT_ADD", $this->lng->txt("meta_add"));
			$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
			$tpl->setVariable("TXT_STATUS", $this->lng->txt("meta_status"));
			$tpl->setVariable("TXT_DRAFT", $this->lng->txt("meta_draft"));
			$tpl->setVariable("TXT_FINAL", $this->lng->txt("meta_final"));
			$tpl->setVariable("TXT_REVISED", $this->lng->txt("meta_revised"));
			$tpl->setVariable("TXT_UNAVAILABLE", $this->lng->txt("meta_unavailable"));
	
			if (is_array($lifecycle = $this->meta_obj->getElement("Lifecycle")))
			{
				$tpl->setVariable("VAL_STATUS_" . strtoupper($lifecycle[0]["Status"]), " selected");
			}
	
			/* Version */
			if (is_array($version = $this->meta_obj->getElement("Version", "Lifecycle")))
			{
				$tpl->setVariable("TXT_VERSION", $this->lng->txt("meta_version"));
				$tpl->setVariable("TXT_VALUE", $this->lng->txt("meta_value"));
				$tpl->setVariable("VAL_VERSION", ilUtil::prepareFormOutput($version[0]["value"]));
				$tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$tpl->setVariable("VAL_VERSION_LANGUAGE", $this->showLangSel("meta[Version][Language]", $version[0]["Language"]));
			}
	
			/* Contribute */
			if (is_array($contribute = $this->meta_obj->getElement("Contribute", "Lifecycle")))
			{
				for ($i = 0; $i < count($contribute); $i++)
				{
					if (is_array($entity = $this->meta_obj->getElement("Entity", "Lifecycle/Contribute", $i)))
					{
						$entities = count($entity);
						for ($j = 0; $j < count($entity); $j++)
						{
							if (count($entity) > 1)
							{
								$tpl->setCurrentBlock("contribute_entity_delete");
								$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Lifecycle/Contribute&meta_name=Entity&meta_index=" . $i . "," . $j);
								$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
								$tpl->parseCurrentBlock();
							}

							$tpl->setCurrentBlock("contribute_entity_loop");
							$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_NO", $j);
							$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_CONTRIBUTE_NO", $i);
							$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_ENTITY", $this->lng->txt("meta_entity"));
							$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
							$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_VAL_ENTITY", ilUtil::prepareFormOutput($entity[$j]["value"]));

							$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Entity&meta_language=" . $a_language . "&meta_path=Lifecycle/Contribute&meta_section=" . $a_section . "&meta_index=" . $i);
							$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
							$tpl->parseCurrentBlock();
						}
					}

					if (count($contribute) > 1)
					{
						$tpl->setCurrentBlock("contribute_delete");
						$tpl->setVariable("CONTRIBUTE_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Lifecycle&meta_name=Contribute&meta_index=" . $i);
						$tpl->setVariable("CONTRIBUTE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
						$tpl->parseCurrentBlock();
					}

					$tpl->setCurrentBlock("contribute_loop");
					$tpl->setVariable("CONTRIBUTE_LOOP_NO", $i);
					$tpl->setVariable("CONTRIBUTE_LOOP_ROWSPAN", (2* $entities) + 2);
					$tpl->setVariable("CONTRIBUTE_LOOP_TXT_CONTRIBUTE", $this->lng->txt("meta_contribute"));
					$tpl->setVariable("CONTRIBUTE_LOOP_TXT_ROLE", $this->lng->txt("meta_role"));
					$tpl->setVariable("CONTRIBUTE_LOOP_TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
					$tpl->setVariable("CONTRIBUTE_LOOP_TXT_ROLE_PUBLISHER", $this->lng->txt("meta_publisher"));
					$tpl->setVariable("CONTRIBUTE_LOOP_TXT_ROLE_AUTHOR", $this->lng->txt("meta_author"));
					$tpl->setVariable("CONTRIBUTE_LOOP_VAL_ROLE_" . strtoupper($contribute[$i]["Role"]), " selected");
					$tpl->setVariable("CONTRIBUTE_LOOP_TXT_DATE", $this->lng->txt("meta_date"));
					if (is_array($date = $this->meta_obj->getElement("Date", "Lifecycle/Contribute", $i)))
					{
						$tpl->setVariable("CONTRIBUTE_LOOP_VAL_DATE", ilUtil::prepareFormOutput($date[0]["value"]));
					}
					$tpl->setVariable("CONTRIBUTE_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Contribute&meta_language=" . $a_language . "&meta_path=Lifecycle&meta_section=" . $a_section);
					$tpl->setVariable("CONTRIBUTE_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$tpl->parseCurrentBlock();
				}
			}
	
			$tpl->setVariable("EDIT_ACTION", $a_formaction . "&cmd=post");
			$tpl->setVariable("VAL_SECTION", $a_section);
			$tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
	
			$this->tpl->setCurrentBlock("lifecycle");
			$this->tpl->setVariable("LIFECYCLE", $tpl->get());
			$this->tpl->parseCurrentBlock();
			unset($tpl);

		}

		return true;
	}

	function fillMetaMetadata($a_formaction, $a_section = "Meta-Metadata", $a_language = "")
	{
		if (!is_array($meta_metadata = $this->meta_obj->getElement("Meta-Metadata")))
		{
			$this->tpl->setCurrentBlock("no_meta_metadata");
			$this->tpl->setVariable("TXT_NO_META_METADATA", $this->lng->txt("meta_no_meta_metadata"));
			$this->tpl->setVariable("TXT_ADD_META_METADATA", $this->lng->txt("meta_add"));
			$this->tpl->setVariable("ACTION_ADD_META_METADATA", $a_formaction . "&cmd=addMeta&meta_name=Meta-Metadata&meta_language=" . $a_language . "&meta_section=" . $a_section);
			$this->tpl->parseCurrentBlock();
		}
		else
		{

			$tpl = new ilTemplate("tpl.meta_data_editor_meta_metadata.html", true, true);
			$tpl->setVariable("TXT_META_METADATA", $this->lng->txt("meta_meta_metadata"));
			$tpl->setVariable("ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_name=Meta-Metadata&meta_index=0");
			$tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));
			$tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
			$tpl->setVariable("TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
			$tpl->setVariable("TXT_CONTRIBUTE", $this->lng->txt("meta_contribute"));
			$tpl->setVariable("TXT_ADD", $this->lng->txt("meta_add"));
			$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
			$tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
			$tpl->setVariable("TXT_METADATASCHEME", $this->lng->txt("meta_metadatascheme"));
	
			if (is_array($meta_metadata = $this->meta_obj->getElement("Meta-Metadata")))
			{
				$tpl->setVariable("VAL_LANGUAGE", $this->showLangSel("meta[Language]", $meta_metadata[0]["Language"]));
				$tpl->setVariable("VAL_METADATASCHEME", ilUtil::stripSlashes($meta_metadata[0]["MetadataScheme"]));
			}
	
			/* Identifier */
			if (is_array($identifier = $this->meta_obj->getElement("Identifier", "Meta-Metadata")))
			{
				for ($i = 0; $i < count($identifier); $i++)
				{
					if (count($identifier) > 1)
					{
						$tpl->setCurrentBlock("identifier_delete");
						$tpl->setVariable("IDENTIFIER_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Meta-Metadata&meta_name=Identifier&meta_index=" . $i);
						$tpl->setVariable("IDENTIFIER_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
						$tpl->parseCurrentBlock();
					}

					$tpl->setCurrentBlock("identifier_loop");
					$tpl->setVariable("IDENTIFIER_LOOP_NO", $i);
					$tpl->setVariable("IDENTIFIER_LOOP_TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
					$tpl->setVariable("IDENTIFIER_LOOP_TXT_CATALOG", $this->lng->txt("meta_catalog"));
					$tpl->setVariable("IDENTIFIER_LOOP_VAL_IDENTIFIER_CATALOG", ilUtil::prepareFormOutput($identifier[$i]["Catalog"]));
					$tpl->setVariable("IDENTIFIER_LOOP_TXT_ENTRY", $this->lng->txt("meta_entry"));
					$tpl->setVariable("IDENTIFIER_LOOP_VAL_IDENTIFIER_ENTRY", ilUtil::prepareFormOutput($identifier[$i]["Entry"]));
					$tpl->setVariable("IDENTIFIER_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Identifier&meta_language=" . $a_language . "&meta_path=Meta-Metadata&meta_section=" . $a_section);
					$tpl->setVariable("IDENTIFIER_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$tpl->parseCurrentBlock();
				}
			}
	
			/* Contribute */
			if (is_array($contribute = $this->meta_obj->getElement("Contribute", "Meta-Metadata")))
			{
				for ($i = 0; $i < count($contribute); $i++)
				{
					if (is_array($entity = $this->meta_obj->getElement("Entity", "Meta-Metadata/Contribute", $i)))
					{
						$entities = count($entity);
						for ($j = 0; $j < count($entity); $j++)
						{
							if (count($entity) > 1)
							{
								$tpl->setCurrentBlock("contribute_delete");
								$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Meta-Metadata/Contribute&meta_name=Entity&&meta_index=" . $i . "," . $j);
								$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
								$tpl->parseCurrentBlock();
							}

							$tpl->setCurrentBlock("contribute_entity_loop");
							$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_NO", $j);
							$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_CONTRIBUTE_NO", $i);
							$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_ENTITY", $this->lng->txt("meta_entity"));
							$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
							$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_VAL_ENTITY", ilUtil::prepareFormOutput($entity[$j]["value"]));

							$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Entity&meta_language=" . $a_language . "&meta_path=Meta-Metadata/Contribute&meta_section=" . $a_section . "&meta_index=" . $i);
							$tpl->setVariable("CONTRIBUTE_ENTITY_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
							$tpl->parseCurrentBlock();
						}
					}

					if (count($contribute) > 1)
					{
						$tpl->setCurrentBlock("contribute_delete");
						$tpl->setVariable("CONTRIBUTE_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Meta-Metadata&meta_name=Contribute&meta_index=" . $i);
						$tpl->setVariable("CONTRIBUTE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
						$tpl->parseCurrentBlock();
					}

					$tpl->setCurrentBlock("contribute_loop");
					$tpl->setVariable("CONTRIBUTE_LOOP_NO", $i);
					$tpl->setVariable("CONTRIBUTE_LOOP_ROWSPAN", ($entities) + 2);
					$tpl->setVariable("CONTRIBUTE_LOOP_TXT_CONTRIBUTE", $this->lng->txt("meta_contribute"));
					$tpl->setVariable("CONTRIBUTE_LOOP_TXT_ROLE", $this->lng->txt("meta_role"));
					$tpl->setVariable("CONTRIBUTE_LOOP_TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
					$tpl->setVariable("CONTRIBUTE_LOOP_TXT_ROLE_PUBLISHER", $this->lng->txt("meta_publisher"));
					$tpl->setVariable("CONTRIBUTE_LOOP_TXT_ROLE_AUTHOR", $this->lng->txt("meta_author"));
					$tpl->setVariable("CONTRIBUTE_LOOP_VAL_ROLE_" . strtoupper($contribute[$i]["Role"]), " selected");
					$tpl->setVariable("CONTRIBUTE_LOOP_TXT_DATE", $this->lng->txt("meta_date"));
					if (is_array($date = $this->meta_obj->getElement("Date", "Meta-Metadata/Contribute", $i)))
					{
						$tpl->setVariable("CONTRIBUTE_LOOP_VAL_DATE", ilUtil::prepareFormOutput($date[0]["value"]));
					}
					$tpl->setVariable("CONTRIBUTE_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Contribute&meta_language=" . $a_language . "&meta_path=Meta-Metadata&meta_section=" . urlencode($a_section));
					$tpl->setVariable("CONTRIBUTE_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$tpl->parseCurrentBlock();
				}
			}
	
			$tpl->setVariable("EDIT_ACTION", $a_formaction . "&cmd=post");
			$tpl->setVariable("VAL_SECTION", $a_section);
			$tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
	
			$this->tpl->setCurrentBlock("meta_metadata");
			$this->tpl->setVariable("META_METADATA", $tpl->get());
			$this->tpl->parseCurrentBlock();
			unset($tpl);

		}

		return true;
	}

	function fillEducational($a_formaction, $a_section = "Educational", $a_language = "")
	{
		if (!is_array($educational = $this->meta_obj->getElement("Educational")))
		{
			$this->tpl->setCurrentBlock("no_educational");
			$this->tpl->setVariable("TXT_NO_EDUCATIONAL", $this->lng->txt("meta_no_educational"));
			$this->tpl->setVariable("TXT_ADD_EDUCATIONAL", $this->lng->txt("meta_add"));
			$this->tpl->setVariable("ACTION_ADD_EDUCATIONAL", $a_formaction . "&cmd=addMeta&meta_name=Educational&meta_language=" . $a_language . "&meta_section=" . $a_section);
			$this->tpl->parseCurrentBlock();
		}
		else
		{

			$tpl = new ilTemplate("tpl.meta_data_editor_educational.html", true, true);
			$tpl->setVariable("TXT_EDUCATIONAL", $this->lng->txt("meta_educational"));
			$tpl->setVariable("ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_name=Educational&meta_index=0");
			$tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));
			$tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
			$tpl->setVariable("TXT_TYPICALAGERANGE", $this->lng->txt("meta_typical_age_range"));
			$tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("meta_description"));
			$tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
			$tpl->setVariable("TXT_ADD", $this->lng->txt("meta_add"));
			$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));

			$tpl->setVariable("TXT_INTERACTIVITYTYPE", $this->lng->txt("meta_interactivity_type"));
			$tpl->setVariable("TXT_LEARNINGRESOURCETYPE", $this->lng->txt("meta_learning_resource_type"));
			$tpl->setVariable("TXT_INTERACTIVITYLEVEL", $this->lng->txt("meta_interactivity_level"));
			$tpl->setVariable("TXT_SEMANTICDENSITY", $this->lng->txt("meta_semantic_density"));
			$tpl->setVariable("TXT_INTENDEDENDUSERROLE", $this->lng->txt("meta_intended_end_user_role"));
			$tpl->setVariable("TXT_CONTEXT", $this->lng->txt("meta_context"));
			$tpl->setVariable("TXT_DIFFICULTY", $this->lng->txt("meta_difficulty"));
			if (is_array($educational = $this->meta_obj->getElement("Educational")))
			{
				$tpl->setVariable("VAL_INTERACTIVITYTYPE_" . strtoupper($educational[0]["InteractivityType"]), " selected");
				$tpl->setVariable("VAL_LEARNINGRESOURCETYPE_" . strtoupper($educational[0]["LearningResourceType"]), " selected");
				$tpl->setVariable("VAL_INTERACTIVITYLEVEL_" . strtoupper($educational[0]["InteractivityLevel"]), " selected");
				$tpl->setVariable("VAL_SEMANTICDENSITY_" . strtoupper($educational[0]["SemanticDensity"]), " selected");
				$tpl->setVariable("VAL_INTENDEDENDUSERROLE_" . strtoupper($educational[0]["IntendedEndUserRole"]), " selected");
				$tpl->setVariable("VAL_CONTEXT_" . strtoupper($educational[0]["Context"]), " selected");
				$tpl->setVariable("VAL_DIFFICULTY_" . strtoupper($educational[0]["Difficulty"]), " selected");
			}

			$tpl->setVariable("TXT_ACTIVE", $this->lng->txt("meta_active"));
			$tpl->setVariable("TXT_EXPOSITIVE", $this->lng->txt("meta_expositive"));
			$tpl->setVariable("TXT_MIXED", $this->lng->txt("meta_mixed"));
			$tpl->setVariable("TXT_EXERCISE", $this->lng->txt("meta_exercise"));
			$tpl->setVariable("TXT_SIMULATION", $this->lng->txt("meta_simulation"));
			$tpl->setVariable("TXT_QUESTIONNAIRE", $this->lng->txt("meta_questionnaire"));
			$tpl->setVariable("TXT_DIAGRAMM", $this->lng->txt("meta_diagramm"));
			$tpl->setVariable("TXT_FIGURE", $this->lng->txt("meta_figure"));
			$tpl->setVariable("TXT_GRAPH", $this->lng->txt("meta_graph"));
			$tpl->setVariable("TXT_INDEX", $this->lng->txt("meta_index"));
			$tpl->setVariable("TXT_SLIDE", $this->lng->txt("meta_slide"));
			$tpl->setVariable("TXT_TABLE", $this->lng->txt("meta_table"));
			$tpl->setVariable("TXT_NARRATIVETEXT", $this->lng->txt("meta_narrative_text"));
			$tpl->setVariable("TXT_EXAM", $this->lng->txt("meta_exam"));
			$tpl->setVariable("TXT_EXPERIMENT", $this->lng->txt("meta_experiment"));
			$tpl->setVariable("TXT_PROBLEMSTATEMENT", $this->lng->txt("meta_problem_statement"));
			$tpl->setVariable("TXT_SELFASSESSMENT", $this->lng->txt("meta_self_assessment"));
			$tpl->setVariable("TXT_LECTURE", $this->lng->txt("meta_lecture"));
			$tpl->setVariable("TXT_VERYLOW", $this->lng->txt("meta_very_low"));
			$tpl->setVariable("TXT_LOW", $this->lng->txt("meta_low"));
			$tpl->setVariable("TXT_MEDIUM", $this->lng->txt("meta_medium"));
			$tpl->setVariable("TXT_HIGH", $this->lng->txt("meta_high"));
			$tpl->setVariable("TXT_VERYHIGH", $this->lng->txt("meta_very_low"));
			$tpl->setVariable("TXT_TEACHER", $this->lng->txt("meta_teacher"));
			$tpl->setVariable("TXT_AUTHOR", $this->lng->txt("meta_author"));
			$tpl->setVariable("TXT_LEARNER", $this->lng->txt("meta_learner"));
			$tpl->setVariable("TXT_MANAGER", $this->lng->txt("meta_manager"));
			$tpl->setVariable("TXT_SCHOOL", $this->lng->txt("meta_school"));
			$tpl->setVariable("TXT_HIGHEREDUCATION", $this->lng->txt("meta_higher_education"));
			$tpl->setVariable("TXT_TRAINING", $this->lng->txt("meta_training"));
			$tpl->setVariable("TXT_OTHER", $this->lng->txt("meta_other"));
			$tpl->setVariable("TXT_VERYEASY", $this->lng->txt("meta_very_easy"));
			$tpl->setVariable("TXT_EASY", $this->lng->txt("meta_easy"));
			$tpl->setVariable("TXT_DIFFICULT", $this->lng->txt("meta_difficult"));
			$tpl->setVariable("TXT_VERYDIFFICULT", $this->lng->txt("meta_very_difficult"));

			/* TypicalAgeRange */
			if (is_array($typicalAgeRange = $this->meta_obj->getElement("TypicalAgeRange", "Educational")))
			{
				for ($i = 0; $i < count($typicalAgeRange); $i++)
				{
					if (count($typicalAgeRange) > 1)
					{
						$tpl->setCurrentBlock("typicalagerange_delete");
						$tpl->setVariable("TYPICALAGERANGE_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Educational&meta_name=TypicalAgeRange&meta_index=" . $i);
						$tpl->setVariable("TYPICALAGERANGE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
						$tpl->parseCurrentBlock();
					}

					$tpl->setCurrentBlock("typicalagerange_loop");
					$tpl->setVariable("TYPICALAGERANGE_LOOP_TXT_TYPICALAGERANGE", $this->lng->txt("meta_typical_age_range"));
					$tpl->setVariable("TYPICALAGERANGE_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
					$tpl->setVariable("TYPICALAGERANGE_LOOP_VAL", ilUtil::prepareFormOutput($typicalAgeRange[$i]["value"]));
					$tpl->setVariable("TYPICALAGERANGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
					$tpl->setVariable("TYPICALAGERANGE_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[TypicalAgeRange][" . $i . "][Language]", $typicalAgeRange[$i]["Language"]));
					$tpl->setVariable("TYPICALAGERANGE_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=TypicalAgeRange&meta_language=" . $a_language . "&meta_path=Educational&meta_section=" . $a_section);
					$tpl->setVariable("TYPICALAGERANGE_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$tpl->parseCurrentBlock();
				}
			}

			/* TypicalLearningTime */
			if (is_array($typicalLearningTime = $this->meta_obj->getElement("TypicalLearningTime", "Educational")))
			{
				$tpl->setVariable("TXT_TYPICALLEARNINGTIME", $this->lng->txt("meta_typical_learning_time"));
				$tpl->setVariable("VAL_TYPICALLEARNINGTIME", ilUtil::prepareFormOutput($typicalLearningTime[0]["value"]));
			}

			/* Description */
			if (is_array($description = $this->meta_obj->getElement("Description", "Educational")))
			{
				for ($i = 0; $i < count($description); $i++)
				{
					$tpl->setCurrentBlock("description_loop");
					$tpl->setVariable("DESCRIPTION_LOOP_NO", $i);
					$tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
					$tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
					$tpl->setVariable("DESCRIPTION_LOOP_VAL", ilUtil::stripSlashes($description[$i]["value"]));
					$tpl->setVariable("DESCRIPTION_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
					$tpl->setVariable("DESCRIPTION_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[Description][" . $i . "][Language]", $description[$i]["Language"]));
					$tpl->setVariable("DESCRIPTION_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Educational&meta_name=Description&meta_index=" . $i);
					$tpl->setVariable("DESCRIPTION_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
					$tpl->setVariable("DESCRIPTION_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Description&meta_language=" . $a_language . "&meta_path=Educational&meta_section=" . $a_section);
					$tpl->setVariable("DESCRIPTION_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$tpl->parseCurrentBlock();
				}
			}

			/* Language */
			if (is_array($language = $this->meta_obj->getElement("Language", "Educational")))
			{
				for ($i = 0; $i < count($language); $i++)
				{
					$tpl->setCurrentBlock("language_loop");
					$tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
					$tpl->setVariable("LANGUAGE_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
					$tpl->setVariable("LANGUAGE_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[Language][" . $i . "][Language]", $language[$i]["Language"]));
	
					$tpl->setVariable("LANGUAGE_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Educational&meta_name=Language&meta_index=" . $i);
					$tpl->setVariable("LANGUAGE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
					$tpl->setVariable("LANGUAGE_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Language&meta_language=" . $a_language . "&meta_path=Educational&meta_section=" . $a_section);
					$tpl->setVariable("LANGUAGE_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$tpl->parseCurrentBlock();
				}
			}

			$tpl->setVariable("EDIT_ACTION", $a_formaction . "&cmd=post");
			$tpl->setVariable("VAL_SECTION", $a_section);
			$tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
	
			$this->tpl->setCurrentBlock("educational");
			$this->tpl->setVariable("EDUCATIONAL", $tpl->get());
			$this->tpl->parseCurrentBlock();
			unset($tpl);

		}

		return true;
	}

	function fillRights($a_formaction, $a_section = "Rights", $a_language = "")
	{
		if (!is_array($rights = $this->meta_obj->getElement("Rights")))
		{
			$this->tpl->setCurrentBlock("no_rights");
			$this->tpl->setVariable("TXT_NO_RIGHTS", $this->lng->txt("meta_no_rights"));
			$this->tpl->setVariable("TXT_ADD_RIGHTS", $this->lng->txt("meta_add"));
			$this->tpl->setVariable("ACTION_ADD_RIGHTS", $a_formaction . "&cmd=addMeta&meta_name=Rights&meta_language=" . $a_language . "&meta_section=" . $a_section);
			$this->tpl->parseCurrentBlock();
		}
		else
		{

			$tpl = new ilTemplate("tpl.meta_data_editor_rights.html", true, true);
			$tpl->setVariable("TXT_RIGHTS", $this->lng->txt("meta_rights"));
			$tpl->setVariable("ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_name=Rights&meta_index=0");
			$tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));

			$tpl->setVariable("TXT_COST", $this->lng->txt("meta_cost"));
			$tpl->setVariable("TXT_COPYRIGHTANDOTHERRESTRICTIONS", $this->lng->txt("meta_copyright_and_other_restrictions"));
			if (is_array($rights = $this->meta_obj->getElement("Rights")))
			{
				$tpl->setVariable("VAL_COST_" . strtoupper($rights[0]["Cost"]), " selected");
				$tpl->setVariable("VAL_COPYRIGHTANDOTHERRESTRICTIONS_" . strtoupper($rights[0]["CopyrightAndOtherRestrictions"]), " selected");
			}

			$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
			$tpl->setVariable("TXT_YES", $this->lng->txt("meta_yes"));
			$tpl->setVariable("TXT_NO", $this->lng->txt("meta_no"));

			/* Description */
			if (is_array($description = $this->meta_obj->getElement("Description", "Rights")))
			{
				for ($i = 0; $i < count($description); $i++)
				{
					$tpl->setCurrentBlock("description_loop");
					$tpl->setVariable("DESCRIPTION_LOOP_NO", $i);
					$tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
					$tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
					$tpl->setVariable("DESCRIPTION_LOOP_VAL", ilUtil::stripSlashes($description[$i]["value"]));
					$tpl->setVariable("DESCRIPTION_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
					$tpl->setVariable("DESCRIPTION_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[Description][" . $i . "][Language]", $description[$i]["Language"]));
					$tpl->parseCurrentBlock();
				}
			}

			$tpl->setVariable("EDIT_ACTION", $a_formaction . "&cmd=post");
			$tpl->setVariable("VAL_SECTION", $a_section);
			$tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
	
			$this->tpl->setCurrentBlock("rights");
			$this->tpl->setVariable("RIGHTS", $tpl->get());
			$this->tpl->parseCurrentBlock();
			unset($tpl);

		}

		return true;
	}

	function fillAnnotation($a_formaction, $a_section = "Annotation", $a_language = "")
	{
		if (!is_array($rights = $this->meta_obj->getElement("Annotation")))
		{
			$this->tpl->setCurrentBlock("no_annotation");
			$this->tpl->setVariable("TXT_NO_ANNOTATION", $this->lng->txt("meta_no_annotation"));
			$this->tpl->setVariable("TXT_ADD_ANNOTATION", $this->lng->txt("meta_add"));
			$this->tpl->setVariable("ACTION_ADD_ANNOTATION", $a_formaction . "&cmd=addMeta&meta_name=Annotation&meta_language=" . $a_language . "&meta_section=" . $a_section);
			$this->tpl->parseCurrentBlock();
		}
		else
		{

			$tpl = new ilTemplate("tpl.meta_data_editor_annotation.html", true, true);
			$tpl->setVariable("TXT_ANNOTATION", $this->lng->txt("meta_annotation"));
			$tpl->setVariable("ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_name=Annotation&meta_index=0");
			$tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));

			$tpl->setVariable("TXT_ENTITY", $this->lng->txt("meta_entity"));
			if (is_array($entity = $this->meta_obj->getElement("Entity", "Annotation")))
			{
				$tpl->setVariable("VAL_ENTITY", ilUtil::prepareFormOutput($entity[0]["value"]));
			}
			$tpl->setVariable("TXT_DATE", $this->lng->txt("meta_date"));
			if (is_array($date = $this->meta_obj->getElement("Date", "Annotation")))
			{
				$tpl->setVariable("VAL_DATE", ilUtil::prepareFormOutput($date[0]["value"]));
			}

			/* Description */
			if (is_array($description = $this->meta_obj->getElement("Description", "Annotation")))
			{
				$tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("meta_description"));
				$tpl->setVariable("TXT_VALUE", $this->lng->txt("meta_value"));
				$tpl->setVariable("VAL_DESCRIPTION", ilUtil::stripSlashes($description[0]["value"]));
				$tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$tpl->setVariable("VAL_DESCRIPTION_LANGUAGE", $this->showLangSel("meta[Description][Language]", $description[0]["Language"]));
			}

			$tpl->setVariable("EDIT_ACTION", $a_formaction . "&cmd=post");
			$tpl->setVariable("VAL_SECTION", $a_section);
			$tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
	
			$this->tpl->setCurrentBlock("annotation");
			$this->tpl->setVariable("ANNOTATION", $tpl->get());
			$this->tpl->parseCurrentBlock();
			unset($tpl);

		}

		return true;
	}

	function fillClassification($a_formaction, $a_section = "Classification", $a_language = "")
	{
		if (!is_array($classification = $this->meta_obj->getElement("Classification")))
		{
			$this->tpl->setCurrentBlock("no_classification");
			$this->tpl->setVariable("TXT_NO_CLASSIFICATION", $this->lng->txt("meta_no_classification"));
			$this->tpl->setVariable("TXT_ADD_CLASSIFICATION", $this->lng->txt("meta_add"));
			$this->tpl->setVariable("ACTION_ADD_CLASSIFICATION", $a_formaction . "&cmd=addMeta&meta_name=Classification&meta_language=" . $a_language . "&meta_section=" . $a_section);
			$this->tpl->parseCurrentBlock();
		}
		else
		{

			$tpl = new ilTemplate("tpl.meta_data_editor_classification.html", true, true);
			$tpl->setVariable("TXT_CLASSIFICATION", $this->lng->txt("meta_classification"));
			$tpl->setVariable("ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_name=Classification&meta_index=0");
			$tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));
			$tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
			$tpl->setVariable("TXT_TAXONPATH", $this->lng->txt("meta_taxon_path"));
			$tpl->setVariable("TXT_KEYWORD", $this->lng->txt("meta_keyword"));
			$tpl->setVariable("TXT_ADD", $this->lng->txt("meta_add"));
			$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));

			$tpl->setVariable("TXT_PURPOSE", $this->lng->txt("meta_purpose"));
			$tpl->setVariable("TXT_DESCIPLINE", $this->lng->txt("meta_learning_resource_type"));
			$tpl->setVariable("TXT_IDEA", $this->lng->txt("meta_idea"));
			$tpl->setVariable("TXT_PREREQUISITE", $this->lng->txt("meta_prerequisite"));
			$tpl->setVariable("TXT_EDUCATIONALOBJECTIVE", $this->lng->txt("meta_educational_objective"));
			$tpl->setVariable("TXT_ACCESSIBILITYRESTRICTIONS", $this->lng->txt("meta_accessibility_restrictions"));
			$tpl->setVariable("TXT_EDUCATIONALLEVEL", $this->lng->txt("meta_educational_level"));
			$tpl->setVariable("TXT_SKILLLEVEL", $this->lng->txt("meta_skill_level"));
			$tpl->setVariable("TXT_SECURITYLEVEL", $this->lng->txt("meta_security_level"));
			$tpl->setVariable("TXT_COMPETENCY", $this->lng->txt("meta_competency"));
			if (is_array($classification = $this->meta_obj->getElement("Classification")))
			{
				$tpl->setVariable("VAL_PURPOSE_" . strtoupper($classification[0]["Purpose"]), " selected");
			}

			/* TaxonPath */
			if (is_array($taxonPath = $this->meta_obj->getElement("TaxonPath", "Classification")))
			{
				for ($i = 0; $i < count($taxonPath); $i++)
				{
					if (is_array($taxon = $this->meta_obj->getElement("Taxon", "Classification/TaxonPath", $i)))
					{
						$taxons = count($taxon);
						for ($j = 0; $j < count($taxon); $j++)
						{
							if (count($taxon) > 1)
							{
								$tpl->setCurrentBlock("taxon_delete");
								$tpl->setVariable("TAXONPATH_TAXON_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Classification/TaxonPath&meta_name=Taxon&meta_index=" . $i . "," . $j);
								$tpl->setVariable("TAXONPATH_TAXON_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
								$tpl->parseCurrentBlock();
							}

							$tpl->setCurrentBlock("taxonpath_taxon_loop");
							$tpl->setVariable("TAXONPATH_TAXON_LOOP_NO", $j);
							$tpl->setVariable("TAXONPATH_TAXON_LOOP_TAXONPATH_NO", $i);
							$tpl->setVariable("TAXONPATH_TAXON_LOOP_TXT_TAXON", $this->lng->txt("meta_taxon"));
							$tpl->setVariable("TAXONPATH_TAXON_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
							$tpl->setVariable("TAXONPATH_TAXON_LOOP_VAL_TAXON", ilUtil::prepareFormOutput($taxon[$j]["value"]));
							$tpl->setVariable("TAXONPATH_TAXON_LOOP_TXT_ID", $this->lng->txt("meta_id"));
							$tpl->setVariable("TAXONPATH_TAXON_LOOP_VAL_ID", ilUtil::prepareFormOutput($taxon[$j]["Id"]));
							$tpl->setVariable("TAXONPATH_TAXON_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
							$tpl->setVariable("TAXONPATH_TAXON_LOOP_VAL_TAXON_LANGUAGE", $this->showLangSel("meta[TaxonPath][" . $i . "][Taxon][" . $j . "][Language]", $taxon[$j]["Language"]));
							$tpl->setVariable("TAXONPATH_TAXON_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Taxon&meta_language=" . $a_language . "&meta_path=Classification/TaxonPath&meta_section=" . $a_section . "&meta_index=" . $i);
							$tpl->setVariable("TAXONPATH_TAXON_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
							$tpl->parseCurrentBlock();
						}
					}

					if (count($taxonPath) > 1)
					{
						$tpl->setCurrentBlock("taxonpath_delete");
						$tpl->setVariable("TAXONPATH_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Classification&meta_name=TaxonPath&meta_index=" . $i);
						$tpl->setVariable("TAXONPATH_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
						$tpl->parseCurrentBlock();
					}

					$tpl->setCurrentBlock("taxonpath_loop");
					$tpl->setVariable("TAXONPATH_LOOP_NO", $i);
					$tpl->setVariable("TAXONPATH_LOOP_ROWSPAN", (3 * $taxons) + 2);
					$tpl->setVariable("TAXONPATH_LOOP_TXT_TAXONPATH", $this->lng->txt("meta_taxon_path"));
					$tpl->setVariable("TAXONPATH_LOOP_TXT_SOURCE", $this->lng->txt("meta_source"));
					$tpl->setVariable("TAXONPATH_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
					$tpl->setVariable("TAXONPATH_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
					if (is_array($source = $this->meta_obj->getElement("Source", "Classification/TaxonPath", $i)))
					{
						$tpl->setVariable("TAXONPATH_LOOP_VAL_SOURCE", ilUtil::prepareFormOutput($source[0]["value"]));
						$tpl->setVariable("TAXONPATH_LOOP_VAL_SOURCE_LANGUAGE", $this->showLangSel("meta[TaxonPath][" . $i . "][Source][Language]", $source[0]["Language"]));
					}
					$tpl->setVariable("TAXONPATH_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=TaxonPath&meta_language=" . $a_language . "&meta_path=Classification&meta_section=" . $a_section);
					$tpl->setVariable("TAXONPATH_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$tpl->parseCurrentBlock();
				}
			}

			/* Description */
			if (is_array($description = $this->meta_obj->getElement("Description", "Classification")))
			{
				$tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("meta_description"));
				$tpl->setVariable("TXT_VALUE", $this->lng->txt("meta_value"));
				$tpl->setVariable("VAL_DESCRIPTION", ilUtil::stripSlashes($description[0]["value"]));
				$tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$tpl->setVariable("VAL_DESCRIPTION_LANGUAGE", $this->showLangSel("meta[Description][Language]", $description[0]["Language"]));
			}

			/* Keyword */
			if (is_array($keyword = $this->meta_obj->getElement("Keyword", "Classification")))
			{
				for ($i = 0; $i < count($keyword); $i++)
				{
					if (count($keyword) > 1)
					{
						$tpl->setCurrentBlock("keyword_delete");
						$tpl->setVariable("KEYWORD_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Classification&meta_name=Keyword&meta_index=" . $i);
						$tpl->setVariable("KEYWORD_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
						$tpl->parseCurrentBlock();
					}

					$tpl->setCurrentBlock("keyword_loop");
					$tpl->setVariable("KEYWORD_LOOP_NO", $i);
					$tpl->setVariable("KEYWORD_LOOP_TXT_KEYWORD", $this->lng->txt("meta_keyword"));
					$tpl->setVariable("KEYWORD_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
					$tpl->setVariable("KEYWORD_LOOP_VAL", ilUtil::prepareFormOutput($keyword[$i]["value"]));
					$tpl->setVariable("KEYWORD_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
					$tpl->setVariable("KEYWORD_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[Keyword][" . $i . "][Language]", $keyword[$i]["Language"]));
					$tpl->setVariable("KEYWORD_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Keyword&meta_language=" . $a_language . "&meta_path=Classification&meta_section=" . $a_section);
					$tpl->setVariable("KEYWORD_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$tpl->parseCurrentBlock();
				}
			}

			$tpl->setVariable("EDIT_ACTION", $a_formaction . "&cmd=post");
			$tpl->setVariable("VAL_SECTION", $a_section);
			$tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
	
			$this->tpl->setCurrentBlock("classification");
			$this->tpl->setVariable("CLASSIFICATION", $tpl->get());
			$this->tpl->parseCurrentBlock();
			unset($tpl);

		}

		return true;
	}

	function fillTechnical($a_formaction, $a_section = "Technical", $a_language = "")
	{
		if (!is_array($technical = $this->meta_obj->getElement("Technical")))
		{
			$this->tpl->setCurrentBlock("no_technical");
			$this->tpl->setVariable("TXT_NO_TECHNICAL", $this->lng->txt("meta_no_technical"));
			$this->tpl->setVariable("TXT_ADD_TECHNICAL", $this->lng->txt("meta_add"));
			$this->tpl->setVariable("ACTION_ADD_TECHNICAL", $a_formaction . "&cmd=addMeta&meta_name=Technical&meta_language=" . $a_language . "&meta_section=" . $a_section);
			$this->tpl->parseCurrentBlock();
		}
		else
		{

			$tpl = new ilTemplate("tpl.meta_data_editor_technical.html", true, true);
			$tpl->setVariable("TXT_TECHNICAL", $this->lng->txt("meta_technical"));
			$tpl->setVariable("ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_name=Technical&meta_index=0");
			$tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));
			$tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
			$tpl->setVariable("TXT_FORMAT", $this->lng->txt("meta_format"));
			$tpl->setVariable("TXT_LOCATION", $this->lng->txt("meta_location"));
			$tpl->setVariable("TXT_ADD", $this->lng->txt("meta_add"));
			$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));

			/* Format */
			if (is_array($format = $this->meta_obj->getElement("Format", "Technical")))
			{
				for ($i = 0; $i < count($format); $i++)
				{
					$tpl->setCurrentBlock("format_loop");
					$tpl->setVariable("FORMAT_LOOP_NO", $i);
					$tpl->setVariable("FORMAT_LOOP_TXT_FORMAT", $this->lng->txt("meta_format"));
					$tpl->setVariable("FORMAT_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
					$tpl->setVariable("FORMAT_LOOP_VAL", ilUtil::prepareFormOutput($format[$i]["value"]));
					$tpl->setVariable("FORMAT_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Technical&meta_name=Format&meta_index=" . $i);
					$tpl->setVariable("FORMAT_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
					$tpl->setVariable("FORMAT_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Format&meta_language=" . $a_language . "&meta_path=Technical&meta_section=" . $a_section);
					$tpl->setVariable("FORMAT_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$tpl->parseCurrentBlock();
				}
			}

			/* Size */
			if (is_array($size = $this->meta_obj->getElement("Size", "Technical")))
			{
				$tpl->setCurrentBlock("size");
				$tpl->setVariable("SIZE_TXT_SIZE", $this->lng->txt("meta_size"));
				$tpl->setVariable("SIZE_TXT_VALUE", $this->lng->txt("meta_value"));
				$tpl->setVariable("SIZE_VAL", ilUtil::prepareFormOutput($size[0]["value"]));
				$tpl->setVariable("SIZE_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Technical&meta_name=Size&meta_index=0");
				$tpl->setVariable("SIZE_TXT_DELETE", $this->lng->txt("meta_delete"));
				$tpl->parseCurrentBlock();
			}
			else
			{
				$tpl->setVariable("TXT_SIZE", $this->lng->txt("meta_size"));
			}

			/* Location */
			if (is_array($location = $this->meta_obj->getElement("Location", "Technical")))
			{
				for ($i = 0; $i < count($location); $i++)
				{
					$tpl->setCurrentBlock("location_loop");
					$tpl->setVariable("LOCATION_LOOP_NO", $i);
					$tpl->setVariable("LOCATION_LOOP_TXT_LOCATION", $this->lng->txt("meta_location"));
					$tpl->setVariable("LOCATION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
					$tpl->setVariable("LOCATION_LOOP_VAL", ilUtil::prepareFormOutput($location[$i]["value"]));
					$tpl->setVariable("LOCATION_LOOP_TXT_TYPE", $this->lng->txt("meta_type"));
					$tpl->setVariable("LOCATION_LOOP_TXT_LOCALFILE", $this->lng->txt("meta_local_file"));
					$tpl->setVariable("LOCATION_LOOP_TXT_REFERENCE", $this->lng->txt("meta_reference"));
					$tpl->setVariable("LOCATION_LOOP_TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
					$tpl->setVariable("LOCATION_LOOP_VAL_TYPE_" . strtoupper($location[$i]["Type"]), " selected");
					$tpl->setVariable("LOCATION_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Technical&meta_name=Location&meta_index=" . $i);
					$tpl->setVariable("LOCATION_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
					$tpl->setVariable("LOCATION_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Location&meta_language=" . $a_language . "&meta_path=Technical&meta_section=" . $a_section);
					$tpl->setVariable("LOCATION_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$tpl->parseCurrentBlock();
				}
			}

			/* Requirement */
			if (is_array($requirement = $this->meta_obj->getElement("Requirement", "Technical")))
			{
				for ($i = 0; $i < count($requirement); $i++)
				{
					$tpl->setCurrentBlock("requirement_loop");
					$tpl->setVariable("REQUIREMENT_LOOP_NO", $i);
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_REQUIREMENT", $this->lng->txt("meta_requirement"));
					$tpl->setVariable("REQUIREMENT_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Technical&meta_name=Requirement&meta_index=" . $i);
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
					$tpl->setVariable("REQUIREMENT_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Requirement&meta_language=" . $a_language . "&meta_path=Technical&meta_section=" . $a_section);
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_TYPE", $this->lng->txt("meta_type"));
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_OPERATINGSYSTEM", $this->lng->txt("meta_operating_system"));
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_NAME", $this->lng->txt("meta_name"));
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_NAME_PCDOS", $this->lng->txt("meta_pc_dos"));
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_NAME_MSWINDOWS", $this->lng->txt("meta_ms_windows"));
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_NAME_MACOS", $this->lng->txt("meta_mac_os"));
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_NAME_UNIX", $this->lng->txt("meta_unix"));
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_NAME_MULTIOS", $this->lng->txt("meta_multi_os"));
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_NAME_NONE", $this->lng->txt("meta_none"));
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_MINIMUMVERSION", $this->lng->txt("meta_minimum_version"));
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_MAXIMUMVERSION", $this->lng->txt("meta_maximum_version"));
					$tpl->setVariable("REQUIREMENT_LOOP_TXT_BROWSER", $this->lng->txt("meta_browser"));
					if (is_array($operatingSystem = $this->meta_obj->getElement("OperatingSystem", "Technical/Requirement/Type", $i)))
					{
						$tpl->setVariable("REQUIREMENT_LOOP_VAL_OPERATINGSYSTEM_NAME_" . strtoupper($operatingSystem[0]["Name"]), " selected");
						$tpl->setVariable("REQUIREMENT_LOOP_VAL_OPERATINGSYSTEM_MINIMUMVERSION", ilUtil::prepareFormOutput($operatingSystem[0]["MinimumVersion"]));
						$tpl->setVariable("REQUIREMENT_LOOP_VAL_OPERATINGSYSTEM_MAXIMUMVERSION", ilUtil::prepareFormOutput($operatingSystem[0]["MaximumVersion"]));
					}
					if (is_array($browser = $this->meta_obj->getElement("Browser", "Technical/Requirement/Type", $i)))
					{
						$tpl->setVariable("REQUIREMENT_LOOP_VAL_BROWSER_NAME_" . strtoupper($browser[0]["Name"]), " selected");
						$tpl->setVariable("REQUIREMENT_LOOP_VAL_BROWSER_MINIMUMVERSION", ilUtil::prepareFormOutput($browser[0]["MinimumVersion"]));
						$tpl->setVariable("REQUIREMENT_LOOP_VAL_BROWSER_MAXIMUMVERSION", ilUtil::prepareFormOutput($browser[0]["MaximumVersion"]));
					}
					$tpl->parseCurrentBlock();
				}
			}
			else
			{
				$tpl->setVariable("TXT_ORCOMPOSITE", $this->lng->txt("meta_or_composite"));
			}

			/* OrComposite */
			if (is_array($orcomposite = $this->meta_obj->getElement("OrComposite", "Technical")))
			{
				for ($i = 0; $i < count($orcomposite); $i++)
				{
					if (is_array($requirement = $this->meta_obj->getElement("Requirement", "Technical/OrComposite", $i)))
					{
						for ($j = 0; $j < count($requirement); $j++)
						{
							if (count($requirement) > 1)
							{
								$tpl->setCurrentBlock("orcomposite_requirement_delete");
								$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Technical/OrComposite&meta_name=Requirement&meta_index=" . $i . "," . $j);
								$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
								$tpl->parseCurrentBlock();
							}

							$tpl->setCurrentBlock("orcomposite_requirement_loop");
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_NO", $j);
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_ORCOMPOSITE_NO", $i);
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_REQUIREMENT", $this->lng->txt("meta_requirement"));
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Requirement&meta_language=" . $a_language . "&meta_path=Technical/OrComposite&meta_section=" . $a_section . "&meta_index=" . $i);
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_TYPE", $this->lng->txt("meta_type"));
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_OPERATINGSYSTEM", $this->lng->txt("meta_operating_system"));
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_NAME", $this->lng->txt("meta_name"));
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_NAME_PCDOS", $this->lng->txt("meta_pc_dos"));
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_NAME_MSWINDOWS", $this->lng->txt("meta_ms_windows"));
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_NAME_MACOS", $this->lng->txt("meta_mac_os"));
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_NAME_UNIX", $this->lng->txt("meta_unix"));
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_NAME_MULTIOS", $this->lng->txt("meta_multi_os"));
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_NAME_NONE", $this->lng->txt("meta_none"));
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_MINIMUMVERSION", $this->lng->txt("meta_minimum_version"));
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_MAXIMUMVERSION", $this->lng->txt("meta_maximum_version"));
							$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_TXT_BROWSER", $this->lng->txt("meta_browser"));
							if (is_array($operatingSystem = $this->meta_obj->getElement("OperatingSystem", "Technical/OrComposite/Requirement/Type", $i)))
							{
								$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_VAL_OPERATINGSYSTEM_NAME_" . strtoupper($operatingSystem[0]["Name"]), " selected");
								$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_VAL_OPERATINGSYSTEM_MINIMUMVERSION", ilUtil::prepareFormOutput($operatingSystem[0]["MinimumVersion"]));
								$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_VAL_OPERATINGSYSTEM_MAXIMUMVERSION", ilUtil::prepareFormOutput($operatingSystem[0]["MaximumVersion"]));
							}
							if (is_array($browser = $this->meta_obj->getElement("Browser", "Technical/OrComposite/Requirement/Type", $i)))
							{
								$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_VAL_BROWSER_NAME_" . strtoupper($browser[0]["Name"]), " selected");
								$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_VAL_BROWSER_MINIMUMVERSION", ilUtil::prepareFormOutput($browser[0]["MinimumVersion"]));
								$tpl->setVariable("ORCOMPOSITE_REQUIREMENT_LOOP_VAL_BROWSER_MAXIMUMVERSION", ilUtil::prepareFormOutput($browser[0]["MaximumVersion"]));
							}
							$tpl->parseCurrentBlock();
						}
					}
					$tpl->setCurrentBlock("orcomposite_loop");
					$tpl->setVariable("ORCOMPOSITE_LOOP_TXT_ORCOMPOSITE", $this->lng->txt("meta_or_composite"));
					$tpl->setVariable("ORCOMPOSITE_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Technical&meta_name=OrComposite&meta_index=" . $i);
					$tpl->setVariable("ORCOMPOSITE_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
					$tpl->setVariable("ORCOMPOSITE_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=OrComposite&meta_language=" . $a_language . "&meta_path=Technical&meta_section=" . $a_section);
					$tpl->setVariable("ORCOMPOSITE_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$tpl->parseCurrentBlock();
				}
			}
			else
			{
				$tpl->setVariable("TXT_REQUIREMENT", $this->lng->txt("meta_requirement"));
			}

			/* InstallationRemarks */
			if (is_array($installationRemarks = $this->meta_obj->getElement("InstallationRemarks", "Technical")))
			{
				$tpl->setCurrentBlock("installationremarks");
				$tpl->setVariable("INSTALLATIONREMARKS_TXT_INSTALLATIONREMARKS", $this->lng->txt("meta_installation_remarks"));
				$tpl->setVariable("INSTALLATIONREMARKS_TXT_VALUE", $this->lng->txt("meta_value"));
				$tpl->setVariable("INSTALLATIONREMARKS_VAL", ilUtil::prepareFormOutput($installationRemarks[0]["value"]));
				$tpl->setVariable("INSTALLATIONREMARKS_TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$tpl->setVariable("INSTALLATIONREMARKS_VAL_LANGUAGE", $this->showLangSel("meta[InstallationRemarks][Language]", $installationRemarks[0]["Language"]));
				$tpl->setVariable("INSTALLATIONREMARKS_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Technical&meta_name=InstallationRemarks&meta_index=0");
				$tpl->setVariable("INSTALLATIONREMARKS_TXT_DELETE", $this->lng->txt("meta_delete"));
				$tpl->parseCurrentBlock();
			}
			else
			{
				$tpl->setVariable("TXT_INSTALLATIONREMARKS", $this->lng->txt("meta_installation_remarks"));
			}

			/* OtherPlattformRequirements */
			if (is_array($otherPlattformRequirements = $this->meta_obj->getElement("OtherPlattformRequirements", "Technical")))
			{
				$tpl->setCurrentBlock("otherplattformrequirements");
				$tpl->setVariable("OTHERPLATTFORMREQUIREMENTS_TXT_OTHERPLATTFORMREQUIREMENTS", $this->lng->txt("meta_other_plattform_requirements"));
				$tpl->setVariable("OTHERPLATTFORMREQUIREMENTS_TXT_VALUE", $this->lng->txt("meta_value"));
				$tpl->setVariable("OTHERPLATTFORMREQUIREMENTS_VAL", ilUtil::prepareFormOutput($otherPlattformRequirements[0]["value"]));
				$tpl->setVariable("OTHERPLATTFORMREQUIREMENTS_TXT_LANGUAGE", $this->lng->txt("meta_language"));
				$tpl->setVariable("OTHERPLATTFORMREQUIREMENTS_VAL_LANGUAGE", $this->showLangSel("meta[OtherPlattformRequirements][Language]", $otherPlattformRequirements[0]["Language"]));
				$tpl->setVariable("OTHERPLATTFORMREQUIREMENTS_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Technical&meta_name=OtherPlattformRequirements&meta_index=0");
				$tpl->setVariable("OTHERPLATTFORMREQUIREMENTS_TXT_DELETE", $this->lng->txt("meta_delete"));
				$tpl->parseCurrentBlock();
			}
			else
			{
				$tpl->setVariable("TXT_OTHERPLATTFORMREQUIREMENTS", $this->lng->txt("meta_other_plattform_requirements"));
			}

			/* Duration */
			if (is_array($duration = $this->meta_obj->getElement("Duration", "Technical")))
			{
				$tpl->setCurrentBlock("duration");
				$tpl->setVariable("DURATION_TXT_DURATION", $this->lng->txt("meta_duration"));
				$tpl->setVariable("DURATION_TXT_VALUE", $this->lng->txt("meta_value"));
				$tpl->setVariable("DURATION_VAL", ilUtil::prepareFormOutput($duration[0]["value"]));
				$tpl->setVariable("DURATION_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Technical&meta_name=Duration&meta_index=0");
				$tpl->setVariable("DURATION_TXT_DELETE", $this->lng->txt("meta_delete"));
				$tpl->parseCurrentBlock();
			}
			else
			{
				$tpl->setVariable("TXT_DURATION", $this->lng->txt("meta_duration"));
			}

			$tpl->setVariable("EDIT_ACTION", $a_formaction . "&cmd=post");
			$tpl->setVariable("VAL_SECTION", $a_section);
			$tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
	
			$this->tpl->setCurrentBlock("technical");
			$this->tpl->setVariable("TECHNICAL", $tpl->get());
			$this->tpl->parseCurrentBlock();
			unset($tpl);

		}

		return true;
	}

	function fillRelation($a_formaction, $a_section = "Relation", $a_language = "")
	{
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

			$tpl = new ilTemplate("tpl.meta_data_editor_relation.html", true, true);
			$tpl->setVariable("TXT_RELATION", $this->lng->txt("meta_relation"));
			$tpl->setVariable("ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_name=Relation&meta_index=0");
			$tpl->setVariable("TXT_DELETE", $this->lng->txt("meta_delete"));
			$tpl->setVariable("TXT_NEW_ELEMENT", $this->lng->txt("meta_new_element"));
			$tpl->setVariable("TXT_KIND", $this->lng->txt("meta_kind"));
			$tpl->setVariable("TXT_PLEASE_SELECT", $this->lng->txt("meta_please_select"));
			$tpl->setVariable("TXT_ISPARTOF", $this->lng->txt("meta_is_part_of"));
			$tpl->setVariable("TXT_HASPART", $this->lng->txt("meta_has_part"));
			$tpl->setVariable("TXT_ISVERSIONOF", $this->lng->txt("meta_is_version_of"));
			$tpl->setVariable("TXT_HASVERSION", $this->lng->txt("meta_has_version"));
			$tpl->setVariable("TXT_ISFORMATOF", $this->lng->txt("meta_is_format_of"));
			$tpl->setVariable("TXT_HASFORMAT", $this->lng->txt("meta_has_format"));
			$tpl->setVariable("TXT_REFERENCES", $this->lng->txt("meta_references"));
			$tpl->setVariable("TXT_ISREFERENCEDBY", $this->lng->txt("meta_is_referenced_by"));
			$tpl->setVariable("TXT_ISBASEDON", $this->lng->txt("meta_is_based_on"));
			$tpl->setVariable("TXT_ISBASISFOR", $this->lng->txt("meta_is_basis_for"));
			$tpl->setVariable("TXT_REQUIRES", $this->lng->txt("meta_requires"));
			$tpl->setVariable("TXT_ISREQUIREDBY", $this->lng->txt("meta_is_required_by"));
			$tpl->setVariable("TXT_RESOURCE", $this->lng->txt("meta_resource"));
			$tpl->setVariable("VAL_KIND_" . strtoupper($relation[0]["Kind"]), " selected");

			/* Identifier_ */
			if (is_array($identifier = $this->meta_obj->getElement("Identifier_", "Relation/Resource")))
			{
				for ($i = 0; $i < count($identifier); $i++)
				{
					if (count($identifier) > 1)
					{
						$tpl->setCurrentBlock("identifier_delete");
						$tpl->setVariable("IDENTIFIER_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Relation/Resource&meta_name=Identifier_&meta_index=" . $i);
						$tpl->setVariable("IDENTIFIER_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
						$tpl->parseCurrentBlock();
					}

					$tpl->setCurrentBlock("identifier_loop");
					$tpl->setVariable("IDENTIFIER_LOOP_NO", $i);
					$tpl->setVariable("IDENTIFIER_LOOP_TXT_IDENTIFIER", $this->lng->txt("meta_identifier"));
					$tpl->setVariable("IDENTIFIER_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Identifier_&meta_language=" . $a_language . "&meta_path=Relation/Resource&meta_section=" . $a_section);
					$tpl->setVariable("IDENTIFIER_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$tpl->setVariable("IDENTIFIER_LOOP_TXT_ENTRY", $this->lng->txt("meta_entry"));
					$tpl->setVariable("IDENTIFIER_LOOP_TXT_CATALOG", $this->lng->txt("meta_catalog"));
					$tpl->setVariable("IDENTIFIER_LOOP_VAL_CATALOG", ilUtil::prepareFormOutput($identifier[$i]["Catalog"]));
					$tpl->setVariable("IDENTIFIER_LOOP_VAL_ENTRY", ilUtil::prepareFormOutput($identifier[$i]["Entry"]));
					$tpl->parseCurrentBlock();
				}
			}

			/* Description */
			if (is_array($description = $this->meta_obj->getElement("Description", "Relation/Resource")))
			{
				for ($i = 0; $i < count($description); $i++)
				{
					if (count($description) > 1)
					{
						$tpl->setCurrentBlock("description_delete");
						$tpl->setVariable("DESCRIPTION_LOOP_ACTION_DELETE", $a_formaction . "&cmd=deleteMeta&meta_section=" . $a_section . "&meta_language=" . $a_language . "&meta_path=Relation/Resource&meta_name=Description&meta_index=" . $i);
						$tpl->setVariable("DESCRIPTION_LOOP_TXT_DELETE", $this->lng->txt("meta_delete"));
						$tpl->parseCurrentBlock();
					}

					$tpl->setCurrentBlock("description_loop");
					$tpl->setVariable("DESCRIPTION_LOOP_NO", $i);
					$tpl->setVariable("DESCRIPTION_LOOP_TXT_DESCRIPTION", $this->lng->txt("meta_description"));
					$tpl->setVariable("DESCRIPTION_LOOP_ACTION_ADD", $a_formaction . "&cmd=addMeta&meta_name=Description&meta_language=" . $a_language . "&meta_path=Relation/Resource&meta_section=" . $a_section);
					$tpl->setVariable("DESCRIPTION_LOOP_TXT_ADD", $this->lng->txt("meta_add"));
					$tpl->setVariable("DESCRIPTION_LOOP_TXT_VALUE", $this->lng->txt("meta_value"));
					$tpl->setVariable("DESCRIPTION_LOOP_TXT_LANGUAGE", $this->lng->txt("meta_language"));
					$tpl->setVariable("DESCRIPTION_LOOP_VAL", ilUtil::stripSlashes($description[$i]["value"]));
					$tpl->setVariable("DESCRIPTION_LOOP_VAL_LANGUAGE", $this->showLangSel("meta[Resource][Description][" . $i . "][Language]", $description[$i]["Language"]));
					$tpl->parseCurrentBlock();
				}
			}

			$tpl->setVariable("EDIT_ACTION", $a_formaction . "&cmd=post");
			$tpl->setVariable("VAL_SECTION", $a_section);
			$tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
	
			$this->tpl->setCurrentBlock("relation");
			$this->tpl->setVariable("RELATION", $tpl->get());
			$this->tpl->parseCurrentBlock();
			unset($tpl);

		}

		return true;
	}

	function edit($a_temp_var, $a_temp_block, $a_formaction, $a_section = "", $a_language = "")
	{
//echo "<br>ilMetaDataGUI::edit-start-";
		if ($a_language == "")
		{
			$a_language = $this->ilias->account->getLanguage();
		}
		$this->tpl->addBlockFile($a_temp_var, $a_temp_block, "tpl.meta_data_editor.html", false);

		if ($a_section == "")
		{
			$a_section = "General";
		}
		$sections = array("General", "Lifecycle", "Meta-Metadata", "Technical",
						  "Educational", "Rights", "Relation", "Annotation", "Classification");
		if (in_array($a_section, $sections))
		{
			$func = "fill" . str_replace("-", "", $a_section);
//echo "<br>ilMetaDataGUI::edit-$func-";
			$this->$func($a_formaction, $a_section, $a_language);
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("CHOOSE_SECTION_ACTION", $a_formaction . "&cmd=chooseMetaSection");
		$this->tpl->setVariable("TXT_CHOOSE_SECTION", $this->lng->txt("meta_choose_section"));
		for ($i = 0; $i < count($sections); $i++)
		{
			if ($a_section != $sections[$i])
			{
				$this->tpl->setVariable("META_SECTION_" . strtoupper($sections[$i]), "in");
			}
		}
		$this->tpl->setVariable("TXT_OK", $this->lng->txt("ok"));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->parseCurrentBlock();
//echo "<br>ilMetaDataGUI::edit-stop-";
	}

	function save($a_section = "General")
	{
		/* editing meta data with editor */
		if (is_array($_POST["meta"]))
		{
			$meta = $_POST["meta"];
			$this->meta_obj->setTitle(ilUtil::stripSlashes($meta["Title"]["Value"]));
			$this->meta_obj->setMeta($meta);
		}
		/* creating a new object -> meta data: title and description */
		else if (is_array($_POST["Fobject"]))
		{
			$meta = $_POST["Fobject"];
			$this->meta_obj->setTitle(ilUtil::stripSlashes($meta["title"]));
			$this->meta_obj->setMeta($meta);
		}
		$this->meta_obj->setSection($a_section);
		$this->obj->updateMetaData();
	}

	function &create()
	{
		$this->meta_obj =& new ilMetaData();
		/* editing meta data with editor */
		if (is_array($_POST["meta"]))
		{
			$meta = $_POST["meta"];
			$this->meta_obj->setTitle(ilUtil::stripSlashes($meta["Title"]["Value"]));
		}
		/* creating a new object -> meta data: title and description */
		else if (is_array($_POST["Fobject"]))
		{
			$meta = $_POST["Fobject"];
			$this->meta_obj->setTitle(ilUtil::stripSlashes($meta["title"]));
		}

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
