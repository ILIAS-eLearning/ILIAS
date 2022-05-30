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
 * TableGUI class for pc image map editor
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCImageMapTableGUI extends ilImageMapTableGUI
{
    protected string $parent_node_name;
    protected ilPCMediaObject $pc_media_object;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilPCMediaObject $a_pc_media_object,
        string $a_parent_node_name
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();

        $this->parent_node_name = $a_parent_node_name;
        $this->pc_media_object = $a_pc_media_object;
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_pc_media_object->getMediaObject());
    }

    public function getItems() : void
    {
        $std_alias_item = new ilMediaAliasItem(
            $this->pc_media_object->dom,
            $this->pc_media_object->hier_id,
            "Standard",
            $this->pc_media_object->getPCId(),
            $this->parent_node_name
        );
        $areas = $std_alias_item->getMapAreas();

        foreach ($areas as $k => $a) {
            $areas[$k]["title"] = $a["Link"]["Title"];
        }
        $areas = ilArrayUtil::sortArray($areas, "title", "asc", false, true);
        $this->setData($areas);
    }
    
    protected function fillRow(array $a_set) : void
    {
        $i = $a_set["Nr"];
        $this->tpl->setVariable(
            "CHECKBOX",
            ilLegacyFormElementsUtil::formCheckbox("", "area[]", $i)
        );
        $this->tpl->setVariable("VAR_NAME", "name_" . $i);
        $this->tpl->setVariable("VAL_NAME", trim($a_set["Link"]["Title"]));
        $this->tpl->setVariable("VAL_SHAPE", $a_set["Shape"]);
        
        $this->tpl->setVariable(
            "VAL_HIGHL_MODE",
            ilLegacyFormElementsUtil::formSelect(
                $a_set["HighlightMode"],
                "hl_mode_" . $i,
                $this->highl_modes,
                false,
                true
            )
        );
        $this->tpl->setVariable(
            "VAL_HIGHL_CLASS",
            ilLegacyFormElementsUtil::formSelect(
                $a_set["HighlightClass"],
                "hl_class_" . $i,
                $this->highl_classes,
                false,
                true
            )
        );
        
        $this->tpl->setVariable(
            "VAL_COORDS",
            implode(", ", explode(",", $a_set["Coords"]))
        );
        switch ($a_set["Link"]["LinkType"]) {
            case "ExtLink":
                $this->tpl->setVariable("VAL_LINK", $a_set["Link"]["Href"]);
                break;

            case "IntLink":
                $link_str = $this->parent_obj->getMapAreaLinkString(
                    $a_set["Link"]["Target"],
                    $a_set["Link"]["Type"],
                    $a_set["Link"]["TargetFrame"]
                );
                $this->tpl->setVariable("VAL_LINK", $link_str);
                break;
        }
    }
}
