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
 * Class ilObjSurveyListGUI
 *
 * @author Alexander Killing <killing@leifos.de>
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class ilObjSurveyListGUI extends ilObjectListGUI
{
    protected ilRbacSystem $rbacsystem;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("survey");
        $this->user = $DIC->user();
        $this->rbacsystem = $DIC->rbac()->system();
        parent::__construct();
        $this->info_screen_enabled = true;
    }

    public function init(): void
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->type = "svy";
        $this->gui_class_name = "ilobjsurveygui";

        // general commands array
        $this->commands = ilObjSurveyAccess::_getCommands();
    }


    public function getProperties(): array
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        $rbacsystem = $this->rbacsystem;

        $props = [];

        if (!$rbacsystem->checkAccess("visible,read", $this->ref_id)) {
            return $props;
        }

        $props = parent::getProperties();

        if (!ilObject::lookupOfflineStatus($this->obj_id)) {
            // BEGIN Usability Distinguish between status and participation
            if (!ilObjSurveyAccess::_lookupCreationComplete($this->obj_id)) {
                // no completion
                $props[] = array("alert" => true,
                    "property" => $lng->txt("svy_participation"),
                    "value" => $lng->txt("svy_warning_survey_not_complete"),
                    'propertyNameVisible' => false);
            } elseif ($ilUser->getId() !== ANONYMOUS_USER_ID) {
                $mode = ilObjSurveyAccess::_lookupMode($this->obj_id);
                if ($mode === ilObjSurvey::MODE_360) {
                    $props[] = array("alert" => false, "property" => $lng->txt("type"),
                                     "value" => $lng->txt("survey_360_mode"), 'propertyNameVisible' => true);
                } elseif ($mode === ilObjSurvey::MODE_SELF_EVAL) {
                    $props[] = array("alert" => false, "property" => $lng->txt("type"),
                                     "value" => $lng->txt("survey_360_self_evaluation"), 'propertyNameVisible' => true);
                } else {
                    $finished = ilObjSurveyAccess::_lookupFinished($this->obj_id, $ilUser->getId());

                    // finished
                    if ($finished === 1) {
                        $stat = $this->lng->txt("svy_finished");
                    }
                    // not finished
                    elseif ($finished === 0) {
                        $stat = $this->lng->txt("svy_not_finished");
                    }
                    // not started
                    else {
                        $stat = $this->lng->txt("svy_not_started");
                    }
                    $props[] = array("alert" => false, "property" => $lng->txt("svy_participation"),
                        "value" => $stat, 'propertyNameVisible' => true);
                }
            }
            // END Usability Distinguish between status and participation
        }

        return $props;
    }

    public function getCommandLink(string $cmd): string
    {
        $link = "ilias.php?baseClass=ilObjSurveyGUI&amp;ref_id=" . $this->ref_id .
            "&amp;cmd=$cmd";

        $this->ctrl->setParameterByClass("ilObjSurveyGUI", "ref_id", $this->ref_id);
        if ($cmd === "questions") {
            $link = $this->ctrl->getLinkTargetByClass(
                [
                "ilObjSurveyGUI", "ilSurveyEditorGUI", "ilSurveyPageEditGUI"],
                "renderPage"
            );
        }
        $this->ctrl->setParameterByClass(
            "ilObjSurveyGUI",
            "ref_id",
            $this->requested_ref_id
        );
        return $link;
    }
}
