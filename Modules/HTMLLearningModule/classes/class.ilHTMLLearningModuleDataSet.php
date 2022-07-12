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
 * HTML learning module data set class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHTMLLearningModuleDataSet extends ilDataSet
{
    protected ilObjFileBasedLM $current_obj;

    public function getSupportedVersions() : array
    {
        return array("4.1.0");
    }
    
    protected function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return "https://www.ilias.de/xml/Modules/HTMLLearningModule/" . $a_entity;
    }
    
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        if ($a_entity === "htlm") {
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
        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        $ilDB = $this->db;

        if ($a_entity === "htlm") {
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

    public function getXmlRecord(string $a_entity, string $a_version, array $a_set) : array
    {
        $lm = new ilObjFileBasedLM($a_set["Id"], false);
        $dir = $lm->getDataDirectory();
        $a_set["Dir"] = $dir;

        return $a_set;
    }

    public function importRecord(string $a_entity, array $a_types, array $a_rec, ilImportMapping $a_mapping, string $a_schema_version) : void
    {
        switch ($a_entity) {
            case "htlm":
                
                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    /** @var ilObjFileBasedLM $newObj */
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
                if ($dir !== "" && $this->getImportDirectory() !== "") {
                    $source_dir = $this->getImportDirectory() . "/" . $dir;
                    $target_dir = $newObj->getDataDirectory();
                    ilFileUtils::rCopy($source_dir, $target_dir);
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
