<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilObjSurveyQuestionPoolListGUI
 *
 * @author Helmut Schottmueller <helmut.schottmueller@mac.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjSurveyQuestionPoolListGUI extends ilObjectListGUI
{
    public function init()
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "spl";
        $this->gui_class_name = "ilobjsurveyquestionpoolgui";

        // general commands array
        $this->commands = ilObjSurveyQuestionPoolAccess::_getCommands();
    }

    public function getCommandFrame($a_cmd)
    {
        $frame = "";
        switch ($a_cmd) {
            case "":
            case "questions":
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;

            default:
        }

        return $frame;
    }

    public function getProperties()
    {
        $lng = $this->lng;
        $ilUser = $this->user;

        $props = array();

        if (!ilObjSurveyQuestionPool::_lookupOnline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("offline"));
        }
        return $props;
    }

    public function getCommandLink($a_cmd)
    {
        return "ilias.php?baseClass=ilObjSurveyQuestionPoolGUI&amp;ref_id=" . $this->ref_id . "&amp;cmd=$a_cmd";
    }
}
