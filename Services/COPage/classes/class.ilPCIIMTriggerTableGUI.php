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
class ilPCIIMTriggerTableGUI extends ilImageMapTableGUI
{
    protected array $pop_options;
    protected array $popups;
    protected array $ov_files;
    protected array $areas;
    protected ilObjMediaObject $mob;
    protected array $ov_options;
    protected string $parent_node_name;
    protected ilPCInteractiveImage $pc_media_object;
    protected array $area;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilPCInteractiveImage $a_pc_media_object,
        string $a_parent_node_name
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $lng = $DIC->language();
        
        $this->setId("cont_iim_tr");

        $this->parent_node_name = $a_parent_node_name;
        $this->pc_media_object = $a_pc_media_object;
        $this->mob = $this->pc_media_object->getMediaObject();
        
        $this->areas = array();
        foreach ($this->pc_media_object->getStandardAliasItem()->getMapAreas() as $a) {
            $this->area[$a["Id"]] = $a;
        }

        $this->ov_files = $this->mob->getFilesOfDirectory("overlays");
        $this->ov_options = array("" => $lng->txt("please_select"));
        foreach ($this->ov_files as $of) {
            $this->ov_options[$of] = $of;
        }
        $this->popups = $this->pc_media_object->getPopups();
        $this->pop_options = array("" => $lng->txt("please_select"));
        foreach ($this->popups as $k => $p) {
            $this->pop_options[$p["nr"]] = $p["title"];
        }
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_pc_media_object->getMediaObject());
        $this->setRowTemplate("tpl.iim_trigger_row.html", "Services/COPage");
    }
    
    public function initColumns() : void
    {
        $this->addColumn("", "", "1");	// checkbox
        $this->addColumn($this->lng->txt("title"), "Title", "");
        $this->addColumn($this->lng->txt("type"), "", "");
        $this->addColumn($this->lng->txt("cont_coords"), "", "");
        $this->addColumn($this->lng->txt("cont_overlay_image"), "", "");
        $this->addColumn($this->lng->txt("cont_content_popup"), "", "");
        $this->addColumn($this->lng->txt("actions"), "", "");
    }

    public function initActions() : void
    {
        $lng = $this->lng;
        
        // action commands
        $this->addMultiCommand("confirmDeleteTrigger", $lng->txt("delete"));
        
        $data = $this->getData();
        if (count($data) > 0) {
            $this->addCommandButton("updateTrigger", $lng->txt("save"), "", "update_tr_button");
        }
    }

    public function getItems() : void
    {
        $triggers = $this->pc_media_object->getTriggers();
        
        $triggers = ilArrayUtil::sortArray($triggers, "Title", "asc", false, true);
        $this->setData($triggers);
    }
    
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;

        $i = $a_set["Nr"];

        // command: edit marker position
        if ($a_set["Overlay"] != "") {
            $this->tpl->setCurrentBlock("cmd");
            $this->tpl->setVariable("CMD_ID", "ov_" . $i);
            $this->tpl->setVariable("HREF_CMD", "#");
            $this->tpl->setVariable("CMD_CLASS", "ov_cmd");
            $this->tpl->setVariable("TXT_CMD", $lng->txt("cont_edit_overlay_position"));
            $this->tpl->parseCurrentBlock();
        }
        
        // command: edit marker position
        if ($a_set["PopupNr"] != "") {
            $this->tpl->setCurrentBlock("cmd");
            $this->tpl->setVariable("CMD_ID", "pop_" . $i);
            $this->tpl->setVariable("HREF_CMD", "#");
            $this->tpl->setVariable("CMD_CLASS", "pop_cmd");
            $this->tpl->setVariable("TXT_CMD", $lng->txt("cont_edit_popup_position"));
            $this->tpl->parseCurrentBlock();
        }
        
        if ($a_set["Type"] == ilPCInteractiveImage::AREA) {
            $this->tpl->setCurrentBlock("coords");
            $this->tpl->setVariable(
                "VAL_COORDS",
                implode(", ", explode(",", $this->area[$a_set["Nr"]]["Coords"]))
            );
            $this->tpl->parseCurrentBlock();
            
            $this->tpl->setVariable(
                "TYPE",
                $lng->txt("cont_" . $this->area[$a_set["Nr"]]["Shape"])
            );
        } else {
            // command: edit marker position
            $this->tpl->setCurrentBlock("cmd");
            $this->tpl->setVariable("CMD_ID", "mark_" . $i);
            $this->tpl->setVariable("HREF_CMD", "#");
            $this->tpl->setVariable("CMD_CLASS", "mark_cmd");
            $this->tpl->setVariable("TXT_CMD", $lng->txt("cont_edit_marker_position"));
            $this->tpl->parseCurrentBlock();
            
            // marker position
            $this->tpl->setCurrentBlock("marker_pos");
            $this->tpl->setVariable("VAR_MARK_POS", "markpos[" . $i . "]");
            $this->tpl->setVariable("ID_MARK_POS", "markpos_" . $i);
            $this->tpl->setVariable("VAL_MARK_POS", $a_set["MarkerX"] . "," . $a_set["MarkerY"]);
            $this->tpl->setVariable("TXT_MLEFT", $lng->txt("cont_left"));
            $this->tpl->setVariable("TXT_MTOP", $lng->txt("cont_top"));
            $this->tpl->parseCurrentBlock();
            
            $this->tpl->setVariable("TYPE", $lng->txt("cont_marker"));
        }

        $this->tpl->setVariable(
            "CHECKBOX",
            ilLegacyFormElementsUtil::formCheckbox("", "tr[]", $i)
        );
        $this->tpl->setVariable("VAR_NAME", "title[" . $i . "]");
        $this->tpl->setVariable("VAL_NAME", $a_set["Title"]);
        
        
        $this->tpl->setVariable("VAR_POS", "ovpos[" . $i . "]");
        $this->tpl->setVariable("ID_OV_POS", "ovpos_" . $i);
        $this->tpl->setVariable("ID_POP_POS", "poppos_" . $i);
        $this->tpl->setVariable("VAR_POP_POS", "poppos[" . $i . "]");
        $this->tpl->setVariable("VAR_POP_SIZE", "popsize[" . $i . "]");
        $this->tpl->setVariable("VAL_POS", $a_set["OverlayX"] . "," . $a_set["OverlayY"]);
        $this->tpl->setVariable("VAL_POP_POS", $a_set["PopupX"] . "," . $a_set["PopupY"]);
        $this->tpl->setVariable("VAL_POP_SIZE", $a_set["PopupWidth"] . "," . $a_set["PopupHeight"]);
        $this->tpl->setVariable("TXT_IMG", $lng->txt("image"));
        $this->tpl->setVariable("TXT_TITLE", $lng->txt("title"));
        $this->tpl->setVariable("TXT_LEFT", $lng->txt("cont_left"));
        $this->tpl->setVariable("TXT_TOP", $lng->txt("cont_top"));
        $this->tpl->setVariable("TXT_WIDTH", $lng->txt("cont_width"));
        $this->tpl->setVariable("TXT_HEIGHT", $lng->txt("cont_height"));
        $this->tpl->setVariable(
            "OVERLAY_IMAGE",
            ilLegacyFormElementsUtil::formSelect($a_set["Overlay"], "ov[" . $i . "]", $this->ov_options, false, true)
        );
        $this->tpl->setVariable(
            "CONTENT_POPUP",
            ilLegacyFormElementsUtil::formSelect($a_set["PopupNr"], "pop[" . $i . "]", $this->pop_options, false, true)
        );
    }
}
