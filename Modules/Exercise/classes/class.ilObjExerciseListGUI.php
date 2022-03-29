<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

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
