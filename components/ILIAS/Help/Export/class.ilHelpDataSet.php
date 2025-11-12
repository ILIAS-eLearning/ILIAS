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

class ilHelpDataSet extends ilDataSet
{
    protected \ILIAS\Help\InternalDomainService $help_domain;
    protected \ILIAS\Help\InternalDataService $help_data;
    protected \ILIAS\Help\Tooltips\TooltipsManager $help_tooltips;
    protected \ILIAS\Help\Map\MapManager $help_map;

    public function __construct()
    {
        global $DIC;

        $this->help_domain = $DIC->help()->internal()->domain();
        $this->help_data = $DIC->help()->internal()->data();

        parent::__construct();

        $this->help_map = $this->help_domain->map();
        $this->help_tooltips = $this->help_domain->tooltips();
    }

    public function getSupportedVersions(): array
    {
        return array("10.0", "4.3.0");
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
                case "10.0":
                    return array(
                        "Chap" => "integer",
                        "Component" => "text",
                        "ScreenId" => "text",
                        "ScreenSubId" => "text",
                        "Perm" => "text",
                        "FullId" => "text"
                    );
            }
        }

        if ($a_entity === "help_tooltip") {
            switch ($a_version) {
                case "4.3.0":
                case "10.0":
                    return array(
                        "Id" => "integer",
                        "TtText" => "text",
                        "TtId" => "text",
                        "Comp" => "text",
                        "Lang" => "text"
                    );
            }
        }

        if ($a_entity === "gdtr") {
            switch ($a_version) {
                case "10.0":
                    return [
                        "ObjId" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Permission" => "integer",
                        "ScreenIds" => "text",
                        "Lang" => "text"
                    ];
            }
        }

        if ($a_entity === "gdtr_step") {
            switch ($a_version) {
                case "10.0":
                    return [
                        "Id" => "integer",
                        "TourId" => "integer",
                        "OrderNr" => "integer",
                        "Type" => "integer",
                        "ElementId" => "text"
                    ];
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
                case "10.0":
                    $this->getDirectDataFromQuery("SELECT chap, component, screen_id, screen_sub_id, perm, full_id " .
                        " FROM help_map " .
                        "WHERE " .
                        $ilDB->in("chap", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity === "help_tooltip") {
            switch ($a_version) {
                case "4.3.0":
                case "10.0":
                    $this->getDirectDataFromQuery("SELECT id, tt_text, tt_id, comp, lang FROM help_tooltip " .
                        " WHERE module_id = " . $ilDB->quote(0, "integer"));
                    break;
            }
        }

        if ($a_entity === "gdtr") {
            switch ($a_version) {
                case "10.0":
                    $this->getDirectDataFromQuery("SELECT hs.obj_id, title, description, permission, screen_ids, lang " .
                        " FROM help_gt_settings hs JOIN object_data od ON (hs.obj_id = od.obj_id)" .
                        "WHERE " .
                        $ilDB->in("hs.obj_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity === "gdtr_step") {
            switch ($a_version) {
                case "10.0":
                    $this->getDirectDataFromQuery("SELECT id, tour_id, order_nr, type, element_id " .
                        " FROM help_gt_step " .
                        "WHERE " .
                        $ilDB->in("id", $a_ids, false, "integer"));
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
        $a_rec = $this->stripTags($a_rec);
        switch ($a_entity) {
            case "help_map":

                // without module ID we do nothing
                $module_id = $a_mapping->getMapping('components/ILIAS/Help', 'help_module', 0);
                $t = $a_mapping->getAllMappings();
                if ($module_id) {
                    $new_chap = $a_mapping->getMapping(
                        'components/ILIAS/Help',
                        'help_chap',
                        $a_rec["Chap"]
                    );

                    // new import (5.1): get chapter from learning module import mapping
                    if ((int) $new_chap === 0) {
                        $new_chap = $a_mapping->getMapping(
                            'components/ILIAS/LearningModule',
                            'lm_tree',
                            $a_rec["Chap"]
                        );
                    }

                    if ($new_chap > 0) {
                        $this->help_map->saveMappingEntry(
                            $new_chap,
                            $a_rec["Component"],
                            $a_rec["ScreenId"],
                            $a_rec["ScreenSubId"],
                            $a_rec["Perm"],
                            $module_id,
                            $a_rec["FullId"] ?? ""
                        );
                    }
                }
                break;

            case "help_tooltip":

                // without module ID we do nothing
                $module_id = $a_mapping->getMapping('components/ILIAS/Help', 'help_module', 0);
                if ($module_id) {
                    $this->help_tooltips->addTooltip($a_rec["TtId"], $a_rec["TtText"], $module_id);
                }
                break;

            case "gdtr":
                $newObj = new ilObjGuidedTour();
                $newObj->create(0, true);

                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->update(true);

                //$this->current_obj = $newObj;
                $a_mapping->addMapping("components/ILIAS/Help", "gdtr", $a_rec["ObjId"], $newObj->getId());
                $a_mapping->addMapping("components/ILIAS/ILIASObject", "obj", $a_rec["ObjId"], $newObj->getId());

                $this->help_domain->guidedTour()->tourSettings()->save(
                    $this->help_data->guidedTour()->settings(
                        $newObj->getId(),
                        false,
                        $a_rec["ScreenIds"],
                        \ILIAS\Help\GuidedTour\Settings\PermissionType::from((int) $a_rec["Permission"]),
                        $a_rec["Lang"],
                    )
                );
                break;

            case "gdtr_step":
                $tour_id = (int) $a_mapping->getMapping("components/ILIAS/Help", "gdtr", $a_rec["TourId"]);
                if ($tour_id > 0) {
                    $step_id = $this->help_domain->guidedTour()->step()->create(
                        $this->help_data->guidedTour()->step(
                            0,
                            $tour_id,
                            (int) $a_rec["OrderNr"],
                            \ILIAS\Help\GuidedTour\Step\StepType::from((int) $a_rec["Type"]),
                            $a_rec["ElementId"],
                        )
                    );
                    $a_mapping->addMapping(
                        "components/ILIAS/COPage",
                        "pg",
                        "gdtr:" . $a_rec["Id"],
                        "gdtr:" . $step_id
                    );
                }
                break;
        }
    }
}
