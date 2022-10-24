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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjMediaPoolListGUI extends ilObjectListGUI
{
    public function init(): void
    {
        $this->copy_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "mep";
        $this->gui_class_name = "ilobjmediapoolgui";

        // general commands array
        $this->commands = ilObjMediaPoolAccess::_getCommands();
    }

    public function getCommandLink(string $cmd): string
    {
        $cmd_link = "ilias.php?baseClass=ilMediaPoolPresentationGUI" .
            "&ref_id=" . $this->ref_id . '&cmd=' . $cmd;
        return $cmd_link;
    }
}
