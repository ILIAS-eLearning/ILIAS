<?php declare(strict_types=1);

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
 * Class ilScorm2004DataSet
 * @author Alexander Killing <killing@leifos.de>
 */
class ilScorm2004DataSet extends ilDataSet
{
    protected array $temp_dir = array();

    /**
     * Note: this is currently used for SCORM authoring lms
     * Get supported versions
     * @return string[]
     */
    public function getSupportedVersions() : array
    {
        return array("5.1.0");
    }

    public function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "http://www.ilias.de/xml/Modules/Scorm2004/" . $a_entity;
    }

    /**
     * @return array<string, class-string<\directory>>|array<string, string>
     */
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        if ($a_entity === "sahs") {
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
        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }

        // sahs
        if ($a_entity === "sahs") {
            $this->data = array();

            switch ($a_version) {
                case "5.1.0":
                    foreach ($a_ids as $sahs_id) {
                        if (ilObject::_lookupType((int) $sahs_id) === "sahs") {
                            $this->data[] = array("Id" => $sahs_id,
                                "Title" => ilObject::_lookupTitle((int) $sahs_id),
                                "Description" => ilObject::_lookupDescription((int) $sahs_id),
                                "Editable" => 1
                            );
                        }
                    }
                    break;

            }
        }
    }


//    /**
//     * Determine the dependent sets of data
//     * @return mixed[]
//     */
//    protected function getDependencies(
//        string $a_entity,
//        string $a_version,
//        ?array $a_rec = null,
//        ?array $a_ids = null
//    ) : array {
//        switch ($a_entity) {
//            case "sahs":
//                return array();
//
//        }
//        return [];
//    }

//    /**
//     * Get xml record
//     * @param string $a_entity
//     * @param string $a_version
//     * @param array  $a_set
//     * @return array
//     * @throws ilFileUtilsException
//     */
//    public function getXmlRecord(string $a_entity, string $a_version, array $a_set) : array
//    {
//        if ($a_entity == "sahs") {
//            // build traditional author export file
//            $lm = new ilObjSCORM2004LearningModule($a_set["Id"], false);
//            $export = new ilScorm2004Export($lm, 'SCORM 2004 3rd');
//            $zip = $export->buildExportFile();
//
//            // move it to temp dir
//            $tmpdir = ilFileUtils::ilTempnam();
//            ilFileUtils::makeDir($tmpdir);
//            $exp_temp = $tmpdir . DIRECTORY_SEPARATOR . basename($zip);
//            ilFileUtils::rename($zip, $exp_temp);
//
//            $this->temp_dir[$a_set["Id"]] = $tmpdir;
//
//            // include temp dir
//            $a_set["Dir"] = $tmpdir;
//            $a_set["File"] = basename($zip);
//        }
//
//        return $a_set;
//    }

    public function afterXmlRecordWriting(string $a_entity, string $a_version, array $a_set) : void
    {
        if ($a_entity === "sahs") {
            // delete our temp dir
            if (isset($this->temp_dir[$a_set["Id"]]) && is_dir($this->temp_dir[$a_set["Id"]])) {
                ilFileUtils::delDir($this->temp_dir[$a_set["Id"]]);
            }
        }
    }


//    /**
//     * Import record
//     * @param string $a_entity
//     * @param array $a_types
//     * @param array $a_rec
//     * @param ilImportMapping $a_mapping
//     * @param string $a_schema_version
//     */
//    public function importRecord(
//        string $a_entity,
//        array $a_types,
//        array $a_rec,
//        ilImportMapping $a_mapping,
//        string $a_schema_version
//    ) : void {
//        switch ($a_entity) {
//            case "sahs":
//                $new_obj_id = $a_mapping->getMapping("Services/Container", "objs", $a_rec["Id"]);
//                $lm = new ilObjSCORM2004LearningModule($new_obj_id, false);
//
////                $lm->setEditable($a_rec["Editable"]);
//                $lm->setImportSequencing(false);
//                $lm->setSequencingExpertMode(false);
//                $lm->setSubType("scorm2004");
//
//                $dir = str_replace("..", "", $a_rec["Dir"]);
//                if ($dir != "" && $this->getImportDirectory() != "") {
//                    $source_dir = $this->getImportDirectory() . "/" . $dir;
//                    $file_path = $lm->getDataDirectory() . "/" . $a_rec["File"];
//                    ilFileUtils::rename($source_dir . "/" . $a_rec["File"], $file_path);
//
//                    ilFileUtils::unzip($file_path);
//                    ilFileUtils::renameExecutables($lm->getDataDirectory());
//                    $title = $lm->readObject();
//                    if ($title != "") {
//                        ilObject::_writeTitle($lm->getId(), $title);
//                    }
//
//                    $lm->setLearningProgressSettingsAtUpload();
//                    $lm->update();
//                }
//                break;
//
//        }
//    }
}
