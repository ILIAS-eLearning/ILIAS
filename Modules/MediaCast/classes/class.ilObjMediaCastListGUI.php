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
 * ListGUI class for media cast objects.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjMediaCastListGUI extends ilObjectListGUI
{
    protected int $child_id;

    public function init() : void
    {
        $this->copy_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "mcst";
        $this->gui_class_name = "ilobjmediacastgui";
        
        // general commands array
        $this->commands = ilObjMediaCastAccess::_getCommands();
    }

    public function getCommandFrame(string $cmd) : string
    {
        switch ($cmd) {
            default:
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;
        }

        return $frame;
    }


    public function getProperties() : array
    {
        $lng = $this->lng;
        $props = array();

        if (!ilObjMediaCastAccess::_lookupOnline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("offline"));
        }

        return $props;
    }

    public function getCommandLink(string $cmd) : string
    {
        // separate method for this line
        $cmd_link = "ilias.php?baseClass=ilMediaCastHandlerGUI&ref_id=" . $this->ref_id . "&cmd=$cmd";

        return $cmd_link;
    }

    public function setChildId(int $a_child_id) : void
    {
        $this->child_id = $a_child_id;
    }

    public function getChildId() : int
    {
        return $this->child_id;
    }
}
