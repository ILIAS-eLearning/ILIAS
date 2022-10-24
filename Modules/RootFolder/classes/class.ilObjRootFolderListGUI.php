<?php

declare(strict_types=1);

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

use ILIAS\RootFolder\StandardGUIRequest;

/**
 * Class ilObjRootFolderListGUI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjRootFolderListGUI extends ilObjectListGUI
{
    protected StandardGUIRequest $root_request;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        parent::__construct();

        $this->root_request = $DIC
            ->rootFolder()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    public function init(): void
    {
        $this->copy_enabled = false;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->type = "root";
        $this->gui_class_name = "ilobjrootfoldergui";

        // general commands array
        $this->commands = ilObjRootFolderAccess::_getCommands();
    }

    public function getCommandLink(string $cmd): string
    {
        global $ilCtrl;

        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
        $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $cmd);
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->root_request->getRefId());

        return $cmd_link;
    }
}
