<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * HTML learning module data set class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesHTMLLearningModule
 */
class ilHTMLLearningModuleDataSet extends ilDataSet
{
    /**
     * Get supported versions
     *
     * @param
     * @return
     */
    public function getSupportedVersions()
    {
        return array("4.1.0");
    }
    
    /**
     * Get xml namespace
     *
     * @param
     * @return
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return "http://www.ilias.de/xml/Modules/HTMLLearningModule/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     *
     * @param
     * @return
     */
    protected function getTypes($a_entity, $a_version)
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
    protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
    {
        return false;
    }
    

    /**
     * Get xml record
     *
     * @param
     * @return
     */
    public function getXmlRecord($a_entity, $a_version, $a_set)
    {
        include_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLM.php");
        $lm = new ilObjFileBasedLM($a_set["Id"], false);
        $dir = $lm->getDataDirectory();
        $a_set["Dir"] = $dir;

        return $a_set;
    }

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
            case "htlm":
                
                include_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLM.php");
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
