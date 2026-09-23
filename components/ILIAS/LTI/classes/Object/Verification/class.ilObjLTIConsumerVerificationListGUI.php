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
 * Presentation of a certificate verification in the personal workspace.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilObjLTIConsumerVerificationListGUI extends ilObjectListGUI
{
    public function init(): void
    {
        $this->type = 'ltiv';
        $this->gui_class_name = ilObjLTIConsumerVerificationGUI::class;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->link_enabled = false;
        $this->subscribe_enabled = false;
        $this->info_screen_enabled = false;
        $this->commands = ilObjLTIConsumerVerificationAccess::_getCommands();
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        global $DIC;

        return [
            [
                'alert' => false,
                'property' => $DIC->language()->txt('type'),
                'value' => $DIC->language()->txt('wsp_list_ltiv'),
            ],
        ];
    }
}
