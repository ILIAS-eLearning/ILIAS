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
		return array("4.4.0", "5.1.0", "5.2.0", "5.4.0");
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
                case "5.4.0":
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
                case "5.4.0":
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
                case "5.4.0":
					return array(
						"ObjId" => "integer",
						"Setting" => "text",
						"Value" => "text");
			}
		}
		if ($a_entity == "common")
		{
			switch ($a_version)
			{
                case "5.4.0":
					return array(
						"ObjId" => "integer"
                    );
			}
		}
		if ($a_entity == "icon")
		{
			switch ($a_version)
			{
                case "5.4.0":
					return array(
						"ObjId" => "integer",
						"Filename" => "text",
						"Dir" => "directory");
			}
		}
		if ($a_entity == "tile")
		{
			switch ($a_version)
			{
                case "5.4.0":
					return array(
						"ObjId" => "integer",
						"Extension" => "text",
						"Dir" => "directory");
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
	    global $DIC;

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
                case "5.4.0":
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
                case "5.4.0":
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
                case "5.4.0":
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
		// common
        if ($a_entity == "common") {
            $this->data = [];
            foreach ($a_ids as $id) {
                $this->data[] = [
                    "ObjId" => $id
                ];
            }
        }
		// tile images
        if ($a_entity == "tile") {
            $cs = $DIC->object()->commonSettings();
            $this->data = [];
            foreach ($a_ids as $id) {
                $ti = $cs->tileImage()->getByObjId($id);
                if ($ti->exists()) {
                    $this->data[] = [
                        "ObjId" => $id,
                        "Extension" => $ti->getExtension(),
                        "Dir" => dirname($ti->getFullPath())
                    ];
                }
            }
        }

        // icons
        if ($a_entity == "icon") {
            $customIconFactory = $DIC['object.customicons.factory'];
            $this->data = [];
            foreach ($a_ids as $id) {
                /** @var ilObjectCustomIcon $customIcon */
                $customIcon = $customIconFactory->getByObjId($id, ilObject::_lookupType($id));
                if ($customIcon->exists()) {
                    $this->data[] = [
                        "ObjId" => $id,
                        "Filename" =>  pathinfo($customIcon->getFullPath(), PATHINFO_BASENAME),
                        "Dir" => dirname($customIcon->getFullPath())
                    ];
                }
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
            case "common":
                return array (
                    "transl" => array("ids" => $a_rec["ObjId"]),
                    "service_settings" => array("ids" => $a_rec["ObjId"]),
                    "tile" => array("ids" => $a_rec["ObjId"]),
                    "icon" => array("ids" => $a_rec["ObjId"])
                );

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
	    global $DIC;

		switch ($a_entity) {
            case "transl_entry":
                $new_id = $this->getNewObjId($a_mapping, $a_rec['ObjId']);
                if ($new_id > 0) {
                    include_once("./Services/Object/classes/class.ilObjectTranslation.php");
                    $transl = ilObjectTranslation::getInstance($new_id);
                    $transl->addLanguage($a_rec["LangCode"], $a_rec["Title"], $a_rec["Description"],
                        $a_rec["LangDefault"], true);
                    $transl->save();
                }
                break;

            case "transl":
                $new_id = $this->getNewObjId($a_mapping, $a_rec['ObjId']);
                if ($new_id > 0) {
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
                $new_id = $this->getNewObjId($a_mapping, $a_rec['ObjId']);
                if ($new_id > 0) {
                    if (in_array($a_rec["Setting"], $settings)) {
                        ilContainer::_writeContainerSetting($new_id, $a_rec["Setting"], $a_rec["Value"]);
                    }
                }
                break;

            case "icon":
                $new_id = $this->getNewObjId($a_mapping, $a_rec['ObjId']);
                $dir = str_replace("..", "", $a_rec["Dir"]);
                if ($dir != "" && $this->getImportDirectory() != "") {
                    $source_dir = $this->getImportDirectory() . "/" . $dir;

                    $customIconFactory = $DIC['object.customicons.factory'];
                    $customIcon = $customIconFactory->getByObjId($new_id, ilObject::_lookupType($new_id));
                    $customIcon->createFromImportDir($source_dir, $a_rec["Filename"]);
                }
                break;

            case "tile":
                $new_id = $this->getNewObjId($a_mapping, $a_rec['ObjId']);
                $dir = str_replace("..", "", $a_rec["Dir"]);
                if ($new_id > 0 && $dir != "" && $this->getImportDirectory() != "") {
                    $source_dir = $this->getImportDirectory() . "/" . $dir;
                    $cs = $DIC->object()->commonSettings();
                    $ti = $cs->tileImage()->getByObjId($new_id);
                    $ti->createFromImportDir($source_dir, $a_rec["Extension"]);
                }
                break;
        }
	}

    /**
     * Get new object id
     *
     * @param ilImportMapping $a_mapping
     * @param $old_id
     * @return mixed
     */
    protected function getNewObjId($a_mapping, $old_id)
    {
        global $DIC;
        
        /** @var ilObjectDefinition $objDefinition */
        $objDefinition = $DIC["objDefinition"];
        
        $new_id = $a_mapping->getMapping('Services/Container','objs',$old_id);
        if (!$new_id)
        {
            $new_id = $a_mapping->getMapping('Services/Object','objs',$old_id);
        }
        if (!$new_id)
        {
            $new_id = $a_mapping->getMapping('Services/Object','obj',$old_id);
        }
        if (!$new_id) {
            foreach ($a_mapping->getAllMappings() as $k => $m) {
                if (substr($k, 0, 8) == "Modules/") {
                    foreach ($m as $type => $map) {
                        if (!$new_id) {
                            if ($objDefinition->isRBACObject($type)) {
                                $new_id = $a_mapping->getMapping($k,$type,$old_id);
                            }
                        }
                    }
                }
            }
        }
        return $new_id;
    }
}
?>