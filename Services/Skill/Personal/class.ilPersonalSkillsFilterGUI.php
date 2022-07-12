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
 ********************************************************************
 */

use ILIAS\Skill\Service\SkillPersonalGUIRequest;

/**
 * Filter for personal skills
 *
 * @author @leifos.de
 * @ingroup
 */
class ilPersonalSkillsFilterGUI
{
    protected ilLanguage $lng;
    protected SkillPersonalGUIRequest $personal_gui_request;
    protected int $requested_formation_type = 0;
    protected bool $requested_target_level = false;
    protected bool $requested_materials_resources = false;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->personal_gui_request = $DIC->skills()->internal()->gui()->personal_request();
        $this->requested_formation_type = $this->personal_gui_request->getTypeOfFormation();
        $this->requested_target_level = $this->personal_gui_request->getShowTargetLevel();
        $this->requested_materials_resources = $this->personal_gui_request->getShowMaterialsResources();
    }

    public function addToToolbar(ilToolbarGUI $toolbar, bool $a_include_target = true) : void
    {
        $lng = $this->lng;

        // type of formation
        $options = array(
            0 => $lng->txt("skmg_all"),
            ilSkillEval::TYPE_APPRAISAL => $lng->txt("skmg_eval_type_1"),
            ilSkillEval::TYPE_MEASUREMENT => $lng->txt("skmg_eval_type_2"),
            ilSkillEval::TYPE_SELF_EVAL => $lng->txt("skmg_eval_type_3"),
            );
        $si = new ilSelectInputGUI($lng->txt("skmg_type_of_formation"), "type_of_formation");
        $si->setOptions($options);
        $si->setValue(ilSession::get("skmg_pf_type_of_formation"));
        $toolbar->addInputItem($si, true);

        if ($a_include_target) {
            // target level
            $options = array(
                0 => $lng->txt("show"),
                1 => $lng->txt("hide")
            );
            $si = new ilSelectInputGUI($lng->txt("skmg_target_level"), "target_level");
            $si->setOptions($options);
            $si->setValue(ilSession::get("skmg_pf_target_level"));
            $toolbar->addInputItem($si, true);
        }

        // materials/ressources
        $options = array(
            0 => $lng->txt("show"),
            1 => $lng->txt("hide")
        );
        $si = new ilSelectInputGUI($lng->txt("skmg_materials_ressources"), "mat_res");
        $si->setOptions($options);
        $si->setValue(ilSession::get("skmg_pf_mat_res"));
        $toolbar->addInputItem($si, true);

        // from
        $from = new ilDateTimeInputGUI($lng->txt("from"), "from");
        if (ilSession::get("skmg_pf_from") != "") {
            $from->setDate(new ilDateTime(ilSession::get("skmg_pf_from"), IL_CAL_DATETIME));
        }
        $toolbar->addInputItem($from, true);

        // to
        $to = new ilDateTimeInputGUI($lng->txt("to"), "to");
        if (ilSession::get("skmg_pf_to") != "") {
            $to->setDate(new ilDateTime(ilSession::get("skmg_pf_to"), IL_CAL_DATETIME));
        }
        $toolbar->addInputItem($to, true);
    }

    /**
     * Save filter values to session
     */
    public function save() : void
    {
        $from = new ilDateTimeInputGUI("", "from");
        $from->checkInput();
        $f = (is_null($from->getDate()))
            ? ""
            : $from->getDate()->get(IL_CAL_DATETIME);
        $to = new ilDateTimeInputGUI("", "to");
        $to->checkInput();
        $t = (is_null($to->getDate()))
            ? ""
            : $to->getDate()->get(IL_CAL_DATETIME);
        ilSession::set("skmg_pf_type_of_formation", $this->requested_formation_type);
        ilSession::set("skmg_pf_target_level", $this->requested_target_level);
        ilSession::set("skmg_pf_mat_res", $this->requested_materials_resources);
        ilSession::set("skmg_pf_from", $f);
        ilSession::set("skmg_pf_to", $t);
    }

    public function isInRange(array $level_entry) : bool
    {
        // from
        if (ilSession::get("skmg_pf_from") != "") {
            if ($level_entry["status_date"] < ilSession::get("skmg_pf_from")) {
                return false;
            }
        }

        // to
        if (ilSession::get("skmg_pf_to") != "") {
            if ($level_entry["status_date"] > ilSession::get("skmg_pf_to")) {
                return false;
            }
        }

        // type
        $type = ilSkillEval::TYPE_APPRAISAL;
        if ($level_entry["self_eval"] == 1) {
            $type = ilSkillEval::TYPE_SELF_EVAL;
        }
        if ($level_entry["trigger_obj_type"] == "tst") {
            $type = ilSkillEval::TYPE_MEASUREMENT;
        }
        if (ilSession::get("skmg_pf_type_of_formation") > 0 && ilSession::get("skmg_pf_type_of_formation") != $type) {
            return false;
        }

        return true;
    }

    public function showTargetLevel() : bool
    {
        return !ilSession::get("skmg_pf_target_level");
    }

    public function showMaterialsRessources() : bool
    {
        return !ilSession::get("skmg_pf_mat_res");
    }
}
