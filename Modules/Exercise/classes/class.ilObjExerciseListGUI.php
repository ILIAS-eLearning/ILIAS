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
 * ListGUI class for exercise objects.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjExerciseListGUI extends ilObjectListGUI
{
    public function init() : void
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "exc";
        $this->gui_class_name = "ilobjexercisegui";

        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }
        
        // general commands array
        $this->commands = ilObjExerciseAccess::_getCommands();
    }

    /**
     * @throws ilDateTimeException
     */
    public function getProperties() : array
    {
        $props = array();
        $rem = ilObjExerciseAccess::_lookupRemainingWorkingTimeString($this->obj_id);
        if ($rem["mtime"] != "") {
            $props[] = array(
                "property" => ($rem["cnt"] > 1)
                    ? $this->lng->txt("exc_next_deadline")
                    : $this->lng->txt("exc_next_deadline_single"),
                "value" => $rem["mtime"]
            );
        }

        return $props;
    }

    public function getCommandLink(string $cmd) : string
    {
        return "ilias.php?baseClass=ilExerciseHandlerGUI&ref_id=" . $this->ref_id . "&cmd=$cmd";
    }
}
