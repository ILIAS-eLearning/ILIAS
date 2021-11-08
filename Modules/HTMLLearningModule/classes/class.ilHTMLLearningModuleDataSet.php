<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * HTML learning module data set class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHTMLLearningModuleDataSet extends ilDataSet
{
    /**
     * Get supported versions
     * @param
     * @return array
     */
    public function getSupportedVersions() : array
    {
        return array("4.1.0");
    }
    
    /**
     * Get xml namespace
     * @param
     * @return string
     */
    public function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "http://www.ilias.de/xml/Modules/HTMLLearningModule/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     * @param
     * @return array
     */
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        if ($a_entity == "htlm") {
            switch ($a_version) {
                case "4.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "StartFile" => "text",
                        "Dir" => "directory");
            }
        }
    }

    /**
     * Read data
     * @param
     * @return void
     */
    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
                
        if ($a_entity == "htlm") {
            switch ($a_version) {
                case "4.1.0":
                    $this->getDirectDataFromQuery("SELECT id, title, description, " .
                        " startfile start_file " .
                        " FROM file_based_lm JOIN object_data ON (file_based_lm.id = object_data.obj_id) " .
                        "WHERE " .
                        $ilDB->in("id", $a_ids, false, "integer"));
                    break;
            }
        }
    }
    
    /**
     * Determine the dependent sets of data
     */
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ) : array {
        return [];
    }
    

    /**
     * Get xml record
     * @param
     * @return array
     */
    public function getXmlRecord(string $a_entity, string $a_version, array $a_set) : array
    {
        $lm = new ilObjFileBasedLM($a_set["Id"], false);
        $dir = $lm->getDataDirectory();
        $a_set["Dir"] = $dir;

        return $a_set;
    }

    /**
     * Import record
     * @param
     * @return void
     */
    public function importRecord(string $a_entity, array $a_types, array $a_rec, ilImportMapping $a_mapping, string $a_schema_version) : void
    {
        //echo $a_entity;
        //var_dump($a_rec);

        switch ($a_entity) {
            case "htlm":
                
                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjFileBasedLM();
                    $newObj->setType("htlm");
                    $newObj->create(true);
                }

                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->setStartFile($a_rec["StartFile"], true);
                $newObj->update(true);
                $this->current_obj = $newObj;

                $dir = str_replace("..", "", $a_rec["Dir"]);
                if ($dir != "" && $this->getImportDirectory() != "") {
                    $source_dir = $this->getImportDirectory() . "/" . $dir;
                    $target_dir = $newObj->getDataDirectory();
                    ilUtil::rCopy($source_dir, $target_dir);
                }

                $a_mapping->addMapping("Modules/HTMLLearningModule", "htlm", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping(
                    "Services/MetaData",
                    "md",
                    $a_rec["Id"] . ":0:htlm",
                    $newObj->getId() . ":0:htlm"
                );
                break;
        }
    }
}
