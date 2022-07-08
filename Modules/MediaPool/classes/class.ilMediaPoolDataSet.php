<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Media Pool Data set class
 *
 * This class implements the following entities:
 * - mep_data: data from table mep_data
 * - mep_tree: data from a join on mep_tree and mep_item
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaPoolDataSet extends ilDataSet
{
    /**
     * @var bool|ilObject|ilObjMediaPool
     */
    protected ?ilObjMediaPool $current_obj = null;
    protected bool $master_lang_only = false;
    protected bool $transl_into = false;
    protected ?ilObjMediaPool $transl_into_mep = null;
    protected string $transl_lang = "";

    public function getSupportedVersions() : array
    {
        return array("5.1.0", "4.1.0");
    }
    
    protected function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "https://www.ilias.de/xml/Modules/MediaPool/" . $a_entity;
    }

    /**
     * Set master language only (export)
     */
    public function setMasterLanguageOnly(bool $a_val) : void
    {
        $this->master_lang_only = $a_val;
    }

    public function getMasterLanguageOnly() : bool
    {
        return $this->master_lang_only;
    }

    public function setTranslationImportMode(
        ?ilObjMediaPool $a_mep,
        string $a_lang = ""
    ) : void {
        if ($a_mep !== null) {
            $this->transl_into = true;
            $this->transl_into_mep = $a_mep;
            $this->transl_lang = $a_lang;
        } else {
            $this->transl_into = false;
        }
    }

    public function getTranslationImportMode() : bool
    {
        return $this->transl_into;
    }

    /**
     * Get translation pool (import)
     */
    public function getTranslationMep() : ?ilObjMediaPool
    {
        return $this->transl_into_mep;
    }

    public function getTranslationLang() : string
    {
        return $this->transl_lang;
    }

    protected function getTypes(string $a_entity, string $a_version) : array
    {
        // mep
        if ($a_entity === "mep") {
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
        if ($a_entity === "mep_tree") {
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
        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        $ilDB = $this->db;

        // mep_data
        if ($a_entity === "mep") {
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
        if ($a_entity === "mep_tree") {
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
    
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ) : array {
        switch ($a_entity) {
            case "mep":
                return array(
                    "mep_tree" => array("ids" => $a_rec["Id"] ?? null)
                );
        }
        return [];
    }
    
    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ) : void {
        //echo $a_entity;
        //var_dump($a_rec);

        switch ($a_entity) {
            case "mep":

                if ($this->getTranslationImportMode()) {
                    return;
                }

                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjMediaPool();
                    $newObj->setType("mep");
                    $newObj->create();
                }
                
                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->setDefaultWidth((int) $a_rec["DefaultWidth"]);
                $newObj->setDefaultHeight((int) $a_rec["DefaultHeight"]);
                $newObj->setForTranslation((bool) ($a_rec["ForTranslation"] ?? false));
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
                                if ($parent === 0) {
                                    $parent = null;
                                }
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
                                if ($parent === 0) {
                                    $parent = null;
                                }
                                $this->current_obj->insertInTree($item->getId(), $parent);
                            }
                            break;

                    }
                } elseif ($a_rec["Type"] === "pg") {
                    $imp_id = explode("_", $a_rec["ImportId"]);
                    if ($imp_id[0] === "il" &&
                        (int) $imp_id[1] == (int) IL_INST_ID &&
                        $imp_id[2] === "pg"
                    ) {
                        $pg_id = $imp_id[3];
                        $pool = ilMediaPoolItem::getPoolForItemId($pg_id);
                        $pool = current($pool);
                        if ($pool == $this->getTranslationMep()->getId()) {
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
                break;
        }
    }
}
