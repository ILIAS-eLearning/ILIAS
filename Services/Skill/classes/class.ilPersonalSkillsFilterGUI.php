<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Filter for personal skills
 *
 * @author @leifos.de
 * @ingroup
 */
class ilPersonalSkillsFilterGUI
{
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
    }

    /**
     * Add to toolbar
     */
    public function addToToolbar(ilToolbarGUI $toolbar, $a_include_target = true)
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
    public function save()
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
        ilSession::set("skmg_pf_type_of_formation", ilUtil::stripSlashes($_POST["type_of_formation"]));
        ilSession::set("skmg_pf_target_level", ilUtil::stripSlashes($_POST["target_level"]));
        ilSession::set("skmg_pf_mat_res", ilUtil::stripSlashes($_POST["mat_res"]));
        ilSession::set("skmg_pf_from", $f);
        ilSession::set("skmg_pf_to", $t);
    }

    /**
     * Is entry in range?
     * @param array $level_data
     * @param array $level_entry
     * @return bool
     */
    public function isInRange($level_data, $level_entry)
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


        //var_dump($level_data);
        //var_dump($level_entry); exit;
        return true;
    }

    /**
     * Show target level?
     * @return bool
     */
    public function showTargetLevel()
    {
        return (int) !ilSession::get("skmg_pf_target_level");
    }

    /**
     * Show materials and ressources?
     * @return bool
     */
    public function showMaterialsRessources()
    {
        return (int) !ilSession::get("skmg_pf_mat_res");
    }
}
