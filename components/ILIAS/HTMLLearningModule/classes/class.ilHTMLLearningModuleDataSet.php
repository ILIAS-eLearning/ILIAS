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

use ILIAS\Dataset\IRSSContainerExportConfig;
use ILIAS\Repository\IRSS\IRSSWrapper;

/**
 * HTML learning module data set class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHTMLLearningModuleDataSet extends ilDataSet
{
    protected ilObjFileBasedLM $current_obj;
    protected IRSSWrapper $irss_wrapper;

    public function __construct()
    {
        global $DIC;

        parent::__construct();
        $this->irss_wrapper = $DIC->htmlLearningModule()->internal()->repo()->irss();
    }

    public function getSupportedVersions(): array
    {
        return array("4.1.0");
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "https://www.ilias.de/xml/Modules/HTMLLearningModule/" . $a_entity;
    }

    protected function getTypes(string $a_entity, string $a_version): array
    {
        if ($a_entity === "htlm") {
            switch ($a_version) {
                case "4.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "StartFile" => "text",
                        "Dir" => "rscontainer");
            }
        }
        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $ilDB = $this->db;

        if ($a_entity === "htlm") {
            switch ($a_version) {
                case "4.1.0":
                    $this->getDirectDataFromQuery("SELECT id, title, description, " .
                        " startfile start_file" .
                        " FROM file_based_lm JOIN object_data ON (file_based_lm.id = object_data.obj_id) " .
                        "WHERE " .
                        $ilDB->in("id", $a_ids, false, "integer"));
                    break;
            }
        }
    }

    public function getXmlRecord(string $a_entity, string $a_version, array $a_set): array
    {
        $lm = new ilObjFileBasedLM($a_set["Id"], false);
        $a_set["Dir"] = $lm->getResource()->getIdentification();

        return $a_set;
    }

    public function getContainerExportConfig(
        array $record,
        string $entity,
        string $schema_version,
        string $field,
        string $value
    ): ?IRSSContainerExportConfig {
        if ($entity === "htlm" && $field === "Dir") {
            $lm = new ilObjFileBasedLM($record["Id"], false);
            $container = $lm->getResource();
            if ($container) {
                return
                    $this->getIRSSContainerExportConfig(
                        $container,
                        ""
                    );
            }
        }
        return null;
    }

    public function importRecord(string $a_entity, array $a_types, array $a_rec, ilImportMapping $a_mapping, string $a_schema_version): void
    {
        $a_rec = $this->stripTags($a_rec);
        switch ($a_entity) {
            case "htlm":

                if ($new_id = $a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_rec['Id'])) {
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

                $dir = str_replace("..", "", $a_rec["Dir"]);
                if ($dir !== "" && $this->getImportDirectory() !== "") {
                    $source_dir = $this->getImportDirectory() . "/" . $dir;
                    $rid = $this->irss_wrapper->createContainerFromLocalDir(
                        $source_dir,
                        new ilHTLMStakeholder(),
                        "",
                        true,
                        $newObj->getTitle()
                    );
                    $newObj->setRID($rid);
                }

                $newObj->update(true);
                $this->current_obj = $newObj;

                $a_mapping->addMapping("components/ILIAS/HTMLLearningModule", "htlm", $a_rec["Id"], $newObj->getId());
                $a_mapping->addMapping(
                    "components/ILIAS/MetaData",
                    "md",
                    $a_rec["Id"] . ":0:htlm",
                    $newObj->getId() . ":0:htlm"
                );
                break;
        }
    }
}
