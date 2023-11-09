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

use ILIAS\Folder\StandardGUIRequest;

/**
 * Class ilObjFolderListGUI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjFolderListGUI extends ilObjectListGUI
{
    protected StandardGUIRequest $folder_request;

    public function init(): void
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->type = "fold";
        $this->gui_class_name = "ilobjfoldergui";

        // general commands array
        $this->commands = ilObjFolderAccess::_getCommands();
        $this->folder_request = $DIC
            ->folder()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    public function getCommandLink(string $cmd): string
    {
        $ilCtrl = $this->ctrl;

        // BEGIN WebDAV: Mount webfolder.
        switch ($cmd) {
            default:

                if ($cmd === 'mount_webfolder' && ilDAVActivationChecker::_isActive()) {
                    global $DIC;
                    $uri_builder = new ilWebDAVUriBuilder($DIC->http()->request());
                    $uri_builder->getUriToMountInstructionModalByRef($this->ref_id);
                    $cmd_link = $uri_builder->getUriToMountInstructionModalByRef($this->ref_id);
                    break;
                }

                // separate method for this line
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $cmd);
                $ilCtrl->setParameterByClass(
                    "ilrepositorygui",
                    "ref_id",
                    $this->folder_request->getRefId()
                );
                break;
        }

        return $cmd_link;
    }
}
