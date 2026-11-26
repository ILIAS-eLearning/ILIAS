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

declare(strict_types=1);

/**
 * GUI class for scorm verification
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilObjSCORMVerificationGUI: ilWorkspaceAccessGUI
 */
class ilObjSCORMVerificationGUI extends ilObject2GUI
{
    public function getType(): string
    {
        return "scov";
    }

    public function create(): void
    {
        $this->redirectRetiredCreationCommand();
    }

    public function save(): void
    {
        $this->redirectRetiredCreationCommand();
    }

    public function createObject(): void
    {
        $this->redirectRetiredCreationCommand();
    }

    public function saveObject(): void
    {
        $this->redirectRetiredCreationCommand();
    }

    private function redirectRetiredCreationCommand(): void
    {
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_create_permission'), true);
        $this->ctrl->redirectByClass(ilPersonalWorkspaceGUI::class);
    }

    public function deliver(): void
    {
        $file = $this->object->getFilePath();
        if ($file) {
            ilFileDelivery::deliverFileLegacy($file, $this->object->getTitle() . ".pdf");
        }
    }

    public function render(bool $a_return = false, string $a_url = ''): string
    {
        global $DIC;
        $ilUser = $DIC->user();
        $lng = $DIC->language();

        if (!$a_return) {
            $this->deliver();
        } else {
            $tree = new ilWorkspaceTree($ilUser->getId());
            $wsp_id = $tree->lookupNodeId($this->object->getId());

            $caption = $lng->txt("wsp_type_scov") . ' "' . $this->object->getTitle() . '"';

            $valid = true;
            $message = '';
            if (!file_exists($this->object->getFilePath())) {
                $valid = false;
                $message = $lng->txt("url_not_found");
            } elseif (!$a_url) {
                $access_handler = new ilWorkspaceAccessHandler($tree);
                if (!$access_handler->checkAccess("read", "", $wsp_id)) {
                    $valid = false;
                    $message = $lng->txt("permission_denied");
                }
            }

            if ($valid) {
                if (!$a_url) {
                    $a_url = $this->getAccessHandler()->getGotoLink($wsp_id, $this->object->getId());
                }
                return '<div><a href="' . $a_url . '">' . $caption . '</a></div>';
            }

            return '<div>' . $caption . ' (' . $message . ')</div>';
        }

        return "";
    }

    public function downloadFromPortfolioPage(ilPortfolioPage $a_page): void
    {
        global $DIC;
        $ilErr = $DIC['ilErr'];

        if (ilPCVerification::isInPortfolioPage($a_page, $this->object->getType(), $this->object->getId())) {
            $this->deliver();
        }

        $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
    }

    public static function _goto(string $a_target): void
    {
        global $DIC;
        $id = explode("_", $a_target);

        $DIC->ctrl->setParameterByClass(
            "ilsharedresourceGUI",
            "wsp_id",
            $id[0]
        );
        $DIC->ctrl->redirectByClass(ilSharedResourceGUI::class);
    }

}
