<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/DataSet/classes/class.ilDataSet.php");

class ilScorm2004DataSet extends ilDataSet
{
    protected $temp_dir = array();

    /**
     * Note: this is currently used for SCORM authoring lms
     *
     * Get supported versions
     *
     * @return string[]
     */
    public function getSupportedVersions()
    {
        return array("5.1.0");
    }

    /**
     * Get xml namespace
     *
     * @param
     * @return
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return "http://www.ilias.de/xml/Modules/Scorm2004/" . $a_entity;
    }

    /**
     * Get field types for entity
     *
     * @param
     * @return
     */
    protected function getTypes($a_entity, $a_version)
    {
        if ($a_entity == "sahs") {
            switch ($a_version) {
                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Editable" => "integer",
                        "Dir" => "directory",
                        "File" => "text"
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

        // sahs
        if ($a_entity == "sahs") {
            $this->data = array();

            switch ($a_version) {
                case "5.1.0":
                    foreach ($a_ids as $sahs_id) {
                        if (ilObject::_lookupType($sahs_id) == "sahs") {
                            $this->data[] = array("Id" => $sahs_id,
                                "Title" => ilObject::_lookupTitle($sahs_id),
                                "Description" => ilObject::_lookupDescription($sahs_id),
                                "Editable" => 1
                            );
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
        switch ($a_entity) {
            case "sahs":
                return array();

        }
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
        if ($a_entity == "sahs") {
            // build traditional author export file
            include_once './Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php';
            $lm = new ilObjSCORM2004LearningModule($a_set["Id"], false);
            $export = new ilScorm2004Export($lm, 'SCORM 2004 3rd');
            $zip = $export->buildExportFile();

            // move it to temp dir
            $tmpdir = ilUtil::ilTempnam();
            ilUtil::makeDir($tmpdir);
            $exp_temp = $tmpdir . DIRECTORY_SEPARATOR . basename($zip);
            ilFileUtils::rename($zip, $exp_temp);

            $this->temp_dir[$a_set["Id"]] = $tmpdir;

            // include temp dir
            $a_set["Dir"] = $tmpdir;
            $a_set["File"] = basename($zip);
        }

        return $a_set;
    }


    /**
     *
     *
     * @param
     * @return
     */
    public function afterXmlRecordWriting($a_entity, $a_schema_version, $d)
    {
        if ($a_entity == "sahs") {
            // delete our temp dir
            if (isset($this->temp_dir[$d["Id"]]) && is_dir($this->temp_dir[$d["Id"]])) {
                ilUtil::delDir($this->temp_dir[$d["Id"]]);
            }
        }
    }


    /**
     * Import record
     * @param $a_entity
     * @param $a_types
     * @param $a_rec
     * @param $a_mapping
     * @param $a_schema_version
     */
    public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
    {
        switch ($a_entity) {
            case "sahs":
                $new_obj_id = $a_mapping->getMapping("Services/Container", "objs", $a_rec["Id"]);
                include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
                $lm = new ilObjSCORM2004LearningModule($new_obj_id, false);

                $lm->setEditable($a_rec["Editable"]);
                $lm->setImportSequencing(false);
                $lm->setSequencingExpertMode(false);
                $lm->setSubType("scorm2004");

                $dir = str_replace("..", "", $a_rec["Dir"]);
                if ($dir != "" && $this->getImportDirectory() != "") {
                    $source_dir = $this->getImportDirectory() . "/" . $dir;
                    $file_path = $lm->getDataDirectory() . "/" . $a_rec["File"];
                    ilFileUtils::rename($source_dir . "/" . $a_rec["File"], $file_path);

                    ilUtil::unzip($file_path);
                    ilUtil::renameExecutables($lm->getDataDirectory());
                    $title = $lm->readObject();
                    if ($title != "") {
                        ilObject::_writeTitle($lm->getId(), $title);
                    }

                    $lm->setLearningProgressSettingsAtUpload();
                    $lm->update();
                }
                break;

        }
    }
}
