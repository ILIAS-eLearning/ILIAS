<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Media Pool Data set class
 *
 * This class implements the following entities:
 * - mep_data: data from table mep_data
 * - mep_tree: data from a join on mep_tree and mep_item
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesMediaPool
 */
class ilMediaPoolDataSet extends ilDataSet
{
    protected $master_lang_only = false;
    protected $transl_into = false;
    protected $transl_into_lm = null;
    protected $transl_lang = "";

    /**
     * Get supported versions
     *
     * @param
     * @return
     */
    public function getSupportedVersions()
    {
        return array("5.1.0", "4.1.0");
    }
    
    /**
     * Get xml namespace
     *
     * @param
     * @return
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return "http://www.ilias.de/xml/Modules/MediaPool/" . $a_entity;
    }

    /**
     * Set master language only (export)
     *
     * @param bool $a_val export only master language
     */
    public function setMasterLanguageOnly($a_val)
    {
        $this->master_lang_only = $a_val;
    }

    /**
     * Get master language only (export)
     *
     * @return bool export only master language
     */
    public function getMasterLanguageOnly()
    {
        return $this->master_lang_only;
    }

    /**
     * Set translation import mode
     *
     * @param ilObjLearningModule $a_lm learning module
     * @param string $a_lang language
     */
    public function setTranslationImportMode($a_lm, $a_lang = "")
    {
        if ($a_lm != null) {
            $this->transl_into = true;
            $this->transl_into_lm = $a_lm;
            $this->transl_lang = $a_lang;
        } else {
            $this->transl_into = false;
        }
    }

    /**
     * Get translation import mode
     *
     * @return bool check if translation import is activated
     */
    public function getTranslationImportMode()
    {
        return $this->transl_into;
    }

    /**
     * Get translation lm (import
     *
     * @return ilObjLearningModule learning module
     */
    public function getTranslationLM()
    {
        return $this->transl_into_lm;
    }

    /**
     * Get translation language (import
     *
     * @return string language
     */
    public function getTranslationLang()
    {
        return $this->transl_lang;
    }

    /**
     * Get field types for entity
     *
     * @param
     * @return
     */
    protected function getTypes($a_entity, $a_version)
    {
        // mep
        if ($a_entity == "mep") {
            switch ($a_version) {
                case "4.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "DefaultWidth" => "integer",
                        "DefaultHeight" => "integer");

                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "DefaultWidth" => "integer",
                        "DefaultHeight" => "integer",
                        "ForTranslation" => "integer"
                    );
            }
        }
    
        // mep_tree
        if ($a_entity == "mep_tree") {
            switch ($a_version) {
                case "4.1.0":
                case "5.1.0":
                        return array(
                            "MepId" => "integer",
                            "Child" => "integer",
                            "Parent" => "integer",
                            "Depth" => "integer",
                            "Type" => "text",
                            "Title" => "text",
                            "ForeignId" => "integer",
                            "ImportId" => "text"
                        );
            }
        }
    }

    /**
     * Read data
     *
     * @param
     * @return
     */
    public function readData($a_entity, $a_version, $a_ids, $a_field = "")
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }

        // mep_data
        if ($a_entity == "mep") {
            switch ($a_version) {
                case "4.1.0":
                    $this->getDirectDataFromQuery("SELECT id, title, description, " .
                        " default_width, default_height" .
                        " FROM mep_data JOIN object_data ON (mep_data.id = object_data.obj_id) " .
                        "WHERE " .
                        $ilDB->in("id", $a_ids, false, "integer"));
                    break;

                case "5.1.0":
                    $q = "SELECT id, title, description, " .
                        " default_width, default_height" .
                        " FROM mep_data JOIN object_data ON (mep_data.id = object_data.obj_id) " .
                        "WHERE " .
                        $ilDB->in("id", $a_ids, false, "integer");

                    $set = $ilDB->query($q);
                    $this->data = array();
                    while ($rec = $ilDB->fetchAssoc($set)) {
                        if ($this->getMasterLanguageOnly()) {
                            $rec["for_translation"] = 1;
                        }
                        $tmp = array();
                        foreach ($rec as $k => $v) {
                            $tmp[$this->convertToLeadingUpper($k)]
                                = $v;
                        }
                        $rec = $tmp;

                        $this->data[] = $rec;
                    }
                    break;

            }
        }

        // mep_tree
        if ($a_entity == "mep_tree") {
            switch ($a_version) {
                case "4.1.0":
                    $this->getDirectDataFromQuery("SELECT mep_id, child " .
                        " ,parent,depth,type,title,foreign_id " .
                        " FROM mep_tree JOIN mep_item ON (child = obj_id) " .
                        " WHERE " .
                        $ilDB->in("mep_id", $a_ids, false, "integer") .
                        " ORDER BY depth");
                    break;

                case "5.1.0":
                    $type = "";
                    if ($this->getMasterLanguageOnly()) {
                        $type = " AND type <> " . $ilDB->quote("mob", "text");
                    }

                    $q = "SELECT mep_id, child " .
                        " ,parent,depth,type,title,foreign_id, import_id " .
                        " FROM mep_tree JOIN mep_item ON (child = obj_id) " .
                        " WHERE " .
                        $ilDB->in("mep_id", $a_ids, false, "integer") .
                        $type .
                        " ORDER BY depth";

                    $set = $ilDB->query($q);
                    $this->data = array();
                    while ($rec = $ilDB->fetchAssoc($set)) {
                        $set2 = $ilDB->query("SELECT for_translation FROM mep_data WHERE id = " . $ilDB->quote($rec["mep_id"], true));
                        $rec2 = $ilDB->fetchAssoc($set2);
                        if (!$rec2["for_translation"]) {
                            $rec["import_id"] = "il_" . IL_INST_ID . "_" . $rec["type"] . "_" . $rec["child"];
                        }
                        $tmp = array();
                        foreach ($rec as $k => $v) {
                            $tmp[$this->convertToLeadingUpper($k)]
                                = $v;
                        }
                        $rec = $tmp;

                        $this->data[] = $rec;
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
        switch ($a_entity) {
            case "mep":
                return array(
                    "mep_tree" => array("ids" => $a_rec["Id"])
                );
        }
        return false;
    }
    
    ////
    //// Needs abstraction (interface?) and version handling
    ////
    
    
    /**
     * Import record
     *
     * @param
     * @return
     */
    public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
    {
        //echo $a_entity;
        //var_dump($a_rec);

        switch ($a_entity) {
            case "mep":

                if ($this->getTranslationImportMode()) {
                    return;
                }

                include_once("./Modules/MediaPool/classes/class.ilObjMediaPool.php");

                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjMediaPool();
                    $newObj->setType("mep");
                    $newObj->create(true);
                }
                
                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->setDefaultWidth($a_rec["DefaultWidth"]);
                $newObj->setDefaultHeight($a_rec["DefaultHeight"]);
                $newObj->setForTranslation($a_rec["ForTranslation"]);
                $newObj->update();
                
                $this->current_obj = $newObj;
                $a_mapping->addMapping("Modules/MediaPool", "mep", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping("Services/Object", "obj", $a_rec["Id"], $newObj->getId());
                break;

            case "mep_tree":
                if (!$this->getTranslationImportMode()) {
                    switch ($a_rec["Type"]) {
                        case "fold":
                            $parent = (int) $a_mapping->getMapping("Modules/MediaPool", "mep_tree", $a_rec["Parent"]);
                            $fold_id =
                                $this->current_obj->createFolder($a_rec["Title"], $parent);
                            $a_mapping->addMapping(
                                "Modules/MediaPool",
                                "mep_tree",
                                $a_rec["Child"],
                                $fold_id
                            );
                            break;

                        case "mob":
                            $parent = (int) $a_mapping->getMapping("Modules/MediaPool", "mep_tree", $a_rec["Parent"]);
                            $mob_id = (int) $a_mapping->getMapping("Services/MediaObjects", "mob", $a_rec["ForeignId"]);
                            $item = new ilMediaPoolItem();
                            $item->setType("mob");
                            $item->setForeignId($mob_id);
                            $item->setImportId($a_rec["ImportId"]);
                            $item->setTitle($a_rec["Title"]);
                            $item->create();
                            if ($item->getId() > 0) {
                                $this->current_obj->insertInTree($item->getId(), $parent);
                            }
                            break;

                        case "pg":
                            $parent = (int) $a_mapping->getMapping("Modules/MediaPool", "mep_tree", $a_rec["Parent"]);

                            $item = new ilMediaPoolItem();
                            $item->setType("pg");
                            $item->setTitle($a_rec["Title"]);
                            $item->setImportId($a_rec["ImportId"]);
                            $item->create();
                            $a_mapping->addMapping("Modules/MediaPool", "pg", $a_rec["Child"], $item->getId());
                            $a_mapping->addMapping(
                                "Services/COPage",
                                "pg",
                                "mep:" . $a_rec["Child"],
                                "mep:" . $item->getId()
                            );
                            if ($item->getId() > 0) {
                                $this->current_obj->insertInTree($item->getId(), $parent);
                            }
                            break;

                    }
                } else {
                    if ($a_rec["Type"] == "pg") {
                        $imp_id = explode("_", $a_rec["ImportId"]);
                        if ($imp_id[0] == "il" &&
                            (int) $imp_id[1] == (int) IL_INST_ID &&
                            $imp_id[2] == "pg"
                        ) {
                            $pg_id = $imp_id[3];
                            include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
                            $pool = ilMediaPoolItem::getPoolForItemId($pg_id);
                            $pool = current($pool);
                            if ($pool == $this->getTranslationLM()->getId()) {
                                $a_mapping->addMapping("Modules/MediaPool", "pg", $a_rec["Child"], $pg_id);
                                $a_mapping->addMapping(
                                    "Services/COPage",
                                    "pg",
                                    "mep:" . $a_rec["Child"],
                                    "mep:" . $pg_id
                                );
                            }
                        }
                    }
                }
                break;
        }
    }
}
