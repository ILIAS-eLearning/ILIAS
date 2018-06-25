<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Object data set class
 * 
 * This class implements the following entities:
 * - transl_entry: data from object_translation
 * - transl: data from obj_content_master_lang
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesObject
 */
class ilObjectDataSet extends ilDataSet
{
	/**
	 * Get supported versions
	 *
	 * @param
	 * @return
	 */
	public function getSupportedVersions()
	{
		return array("4.4.0", "5.1.0", "5.2.0");
	}
	
	/**
	 * Get xml namespace
	 *
	 * @param
	 * @return
	 */
	function getXmlNamespace($a_entity, $a_schema_version)
	{
		return "http://www.ilias.de/xml/Services/Object/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		if ($a_entity == "transl_entry")
		{
			switch ($a_version)
			{
				case "4.4.0":
				case "5.1.0":
				case "5.2.0":
					return array(
						"ObjId" => "integer",
						"Title" => "text",
						"Description" => "text",
						"LangCode" => "text",
						"LangDefault" => "integer");
			}
		}
		if ($a_entity == "transl")
		{
			switch ($a_version)
			{
				case "4.4.0":
				case "5.1.0":
				case "5.2.0":
					return array(
						"ObjId" => "integer",
						"MasterLang" => "text");
			}
		}
		if ($a_entity == "service_settings")
		{
			switch ($a_version)
			{
				case "5.1.0":
				case "5.2.0":
					return array(
						"ObjId" => "integer",
						"Setting" => "text",
						"Value" => "text");
			}
		}
	}

	/**
	 * Read data
	 *
	 * @param
	 * @return
	 */
	function readData($a_entity, $a_version, $a_ids, $a_field = "")
	{
		$ilDB = $this->db;

		if (!is_array($a_ids))
		{
			$a_ids = array($a_ids);
		}
				
		if ($a_entity == "transl_entry")
		{
			switch ($a_version)
			{
				case "4.4.0":
				case "5.1.0":
				case "5.2.0":
					$this->getDirectDataFromQuery("SELECT obj_id, title, description,".
						" lang_code, lang_default".
						" FROM object_translation".
						" WHERE ".$ilDB->in("obj_id", $a_ids, false, "integer"));
					break;
			}
		}

		if ($a_entity == "transl")
		{
			switch ($a_version)
			{
				case "4.4.0":
				case "5.1.0":
				case "5.2.0":
					$this->getDirectDataFromQuery("SELECT obj_id, master_lang".
						" FROM obj_content_master_lng".
						" WHERE ".$ilDB->in("obj_id", $a_ids, false, "integer"));
					break;
			}
		}

		if ($a_entity == "service_settings")
		{
			switch ($a_version)
			{
				case "5.1.0":
				case "5.2.0":
					include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
					include_once("./Services/Container/classes/class.ilContainer.php");

					$this->data = array();
					foreach ($a_ids as $id)
					{
						// info, news, custom metadata, tags, taxonomies, auto rating (all stored in container settings)
						$settings = array(
							ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
							ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
							ilObjectServiceSettingsGUI::CUSTOM_METADATA,
							ilObjectServiceSettingsGUI::TAG_CLOUD,
							ilObjectServiceSettingsGUI::TAXONOMIES,
							ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS,
							ilObjectServiceSettingsGUI::CALENDAR_VISIBILITY
						);
						if ($a_version == "5.2.0")
						{
							$settings[] = ilObjectServiceSettingsGUI::USE_NEWS;
						}
						foreach ($settings as $s)
						{
							$val = ilContainer::_lookupContainerSetting($id, $s);
							if ($val)
							{
								$this->data[] = array(
									"ObjId" => $id,
									"Setting" => $s,
									"Value" => $val
								);
							}
						}
					}
					break;
			}
		}

	}
	
	/**
	 * Determine the dependent sets of data 
	 */
	protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
	{
		switch ($a_entity)
		{
			case "transl":
				return array (
					"transl_entry" => array("ids" => $a_rec["ObjId"])
				);
		}

		return false;
	}
	
	
	/**
	 * Import record
	 *
	 * @param
	 * @return
	 */
	function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
	{
		switch ($a_entity)
		{
			case "transl_entry":
				$new_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['ObjId']);
				if (!$new_id)
				{
					$new_id = $a_mapping->getMapping('Services/Object','obj',$a_rec['ObjId']);
				}
				if ($new_id > 0)
				{
					include_once("./Services/Object/classes/class.ilObjectTranslation.php");
					$transl = ilObjectTranslation::getInstance($new_id);
					$transl->addLanguage($a_rec["LangCode"], $a_rec["Title"], $a_rec["Description"], $a_rec["LangDefault"], true);
					$transl->save();
				}
				break;

			case "transl":
				$new_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['ObjId']);
				if (!$new_id)
				{
					$new_id = $a_mapping->getMapping('Services/Object','obj',$a_rec['ObjId']);
				}
				if ($new_id > 0)
				{
					include_once("./Services/Object/classes/class.ilObjectTranslation.php");
					$transl = ilObjectTranslation::getInstance($new_id);
					$transl->setMasterLanguage($a_rec["MasterLang"]);
					$transl->save();
				}
				break;

			case "service_settings":
				include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
				include_once("./Services/Container/classes/class.ilContainer.php");

				// info, news, custom metadata, tags, taxonomies, auto rating (all stored in container settings)
				$settings = array(
					ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
					ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
					ilObjectServiceSettingsGUI::CUSTOM_METADATA,
					ilObjectServiceSettingsGUI::TAG_CLOUD,
					ilObjectServiceSettingsGUI::TAXONOMIES,
					ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS,
					ilObjectServiceSettingsGUI::CALENDAR_VISIBILITY,
					ilObjectServiceSettingsGUI::USE_NEWS
				);
				$new_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['ObjId']);
				if (!$new_id)
				{
					$new_id = $a_mapping->getMapping('Services/Object','objs',$a_rec['ObjId']);
				}
				if (!$new_id)
				{
					$new_id = $a_mapping->getMapping('Services/Object','obj',$a_rec['ObjId']);
				}
				if ($new_id > 0)
				{
					if (in_array($a_rec["Setting"], $settings))
					{
						ilContainer::_writeContainerSetting($new_id, $a_rec["Setting"], $a_rec["Value"]);
					}
				}
				break;
		}
	}
}
?>