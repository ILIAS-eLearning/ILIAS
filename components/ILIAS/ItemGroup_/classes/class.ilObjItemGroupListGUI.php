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
 * Item group list gui class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjItemGroupListGUI extends ilObjectListGUI
{
    protected bool $subitems_enabled;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $lng = $DIC->language();

        $lng->loadLanguageModule('itgr');
        parent::__construct();
    }

    public function init(): void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = false;
        $this->copy_enabled = false;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = false;
        $this->subitems_enabled = true;
        $this->type = "itgr";
        $this->gui_class_name = "ilobjitemgroupgui";

        // general commands array
        $this->commands = ilObjItemGroupAccess::_getCommands();
    }

    public function enableSubscribe(bool $status): void
    {
        $this->subscribe_enabled = false;
    }

    /**
     * Prevent enabling info
     * necessary due to bug 11509
     */
    public function enableInfoScreen(bool $info_screen): void
    {
        $this->info_screen_enabled = false;
    }

    public function getCommandLink(string $cmd): string
    {
        $ilCtrl = $this->ctrl;

        // separate method for this line
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
        $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $cmd);
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->requested_ref_id);
        return $cmd_link;
    }

    public function getProperties(): array
    {
        $props = array();
        return $props;
    }
}
