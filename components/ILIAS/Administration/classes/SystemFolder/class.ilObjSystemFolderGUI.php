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

use ILIAS\UICore\GlobalTemplate;

class ilObjSystemFolderGUI extends ilObject2GUI
{
    public function getType(): string
    {
        return 'adm';
    }

    public function executeCommand(): void
    {
        $this->lng->loadLanguageModule("administration");
        $this->prepareOutput();
        $this->viewObject();
    }

    public function getAdminTabs(): void
    {
        // no tabs
    }

    public function viewObject(): void
    {
        $this->tpl->setOnScreenMessage(GlobalTemplate::MESSAGE_TYPE_INFO, $this->lng->txt('system_folder_info'));
    }

    public static function _goto(): void
    {
        global $DIC;

        $DIC->ctrl()->setParameterByClass(self::class, 'ref_id', SYSTEM_FOLDER_ID);
        $DIC->ctrl()->redirectByClass([ilAdministrationGUI::class, self::class]);
    }
}
