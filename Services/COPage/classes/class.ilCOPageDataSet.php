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
 * COPage Data set class
 * This class implements the following entities:
 * - pgtp: page layout template
 * Please note that the usual page xml export DOES NOT use the dataset.
 * The page export uses pre-existing methods to create the xml.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCOPageDataSet extends ilDataSet
{
    protected ilPageLayout $current_obj;
    protected bool $master_lang_only = false;

    public function setMasterLanguageOnly(bool $a_val): void
    {
        $this->master_lang_only = $a_val;
    }

    public function getMasterLanguageOnly(): bool
    {
        return $this->master_lang_only;
    }

    public function getSupportedVersions(): array
    {
        return array("4.2.0");
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "https://www.ilias.de/xml/Services/COPage/" . $a_entity;
    }

    protected function getTypes(string $a_entity, string $a_version): array
    {
        // pgtp: page layout template
        if ($a_entity == "pgtp") {
            switch ($a_version) {
                case "4.2.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "SpecialPage" => "integer",
                        "StyleId" => "integer");
            }
        }
        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $db = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }

        // mep_data
        if ($a_entity == "pgtp") {
            switch ($a_version) {
                case "4.2.0":
                    $this->getDirectDataFromQuery("SELECT layout_id id, title, description, " .
                        " style_id, special_page " .
                        " FROM page_layout " .
                        "WHERE " .
                        $db->in("layout_id", $a_ids, false, "integer"));
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
    ): array {
        return [];
    }

    public function importRecord(string $a_entity, array $a_types, array $a_rec, ilImportMapping $a_mapping, string $a_schema_version): void
    {
        switch ($a_entity) {
            case "pgtp":
                $pt = new ilPageLayout();
                $pt->setTitle($a_rec["Title"]);
                $pt->setDescription($a_rec["Description"]);
                $pt->setSpecialPage($a_rec["SpecialPage"]);
                $pt->update();

                $this->current_obj = $pt;
                $a_mapping->addMapping(
                    "Services/COPage",
                    "pgtp",
                    $a_rec["Id"],
                    $pt->getId()
                );
                $a_mapping->addMapping(
                    "Services/COPage",
                    "pg",
                    "stys:" . $a_rec["Id"],
                    "stys:" . $pt->getId()
                );
                break;
        }
    }
}
