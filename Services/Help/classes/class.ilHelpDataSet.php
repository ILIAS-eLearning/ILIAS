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
 * Help system data set class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHelpDataSet extends ilDataSet
{
    public function getSupportedVersions(): array
    {
        return array("4.3.0");
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "https://www.ilias.de/xml/Services/Help/" . $a_entity;
    }

    protected function getTypes(string $a_entity, string $a_version): array
    {
        if ($a_entity === "help_map") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "Chap" => "integer",
                        "Component" => "text",
                        "ScreenId" => "text",
                        "ScreenSubId" => "text",
                        "Perm" => "text"
                    );
            }
        }

        if ($a_entity === "help_tooltip") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "Id" => "integer",
                        "TtText" => "text",
                        "TtId" => "text",
                        "Comp" => "text",
                        "Lang" => "text"
                    );
            }
        }
        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $ilDB = $this->db;

        if ($a_entity === "help_map") {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT chap, component, screen_id, screen_sub_id, perm " .
                        " FROM help_map " .
                        "WHERE " .
                        $ilDB->in("chap", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity === "help_tooltip") {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT id, tt_text, tt_id, comp, lang FROM help_tooltip");
                    break;
            }
        }
    }

    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ): void {
        switch ($a_entity) {
            case "help_map":

                // without module ID we do nothing
                $module_id = $a_mapping->getMapping('Services/Help', 'help_module', 0);
                $t = $a_mapping->getAllMappings();
                if ($module_id) {
                    $new_chap = $a_mapping->getMapping(
                        'Services/Help',
                        'help_chap',
                        $a_rec["Chap"]
                    );

                    // new import (5.1): get chapter from learning module import mapping
                    if ((int) $new_chap === 0) {
                        $new_chap = $a_mapping->getMapping(
                            'Modules/LearningModule',
                            'lm_tree',
                            $a_rec["Chap"]
                        );
                    }

                    if ($new_chap > 0) {
                        ilHelpMapping::saveMappingEntry(
                            $new_chap,
                            $a_rec["Component"],
                            $a_rec["ScreenId"],
                            $a_rec["ScreenSubId"],
                            $a_rec["Perm"],
                            $module_id
                        );
                    }
                }
                break;

            case "help_tooltip":

                // without module ID we do nothing
                $module_id = $a_mapping->getMapping('Services/Help', 'help_module', 0);
                if ($module_id) {
                    ilHelp::addTooltip($a_rec["TtId"], $a_rec["TtText"], $module_id);
                }
                break;
        }
    }
}
