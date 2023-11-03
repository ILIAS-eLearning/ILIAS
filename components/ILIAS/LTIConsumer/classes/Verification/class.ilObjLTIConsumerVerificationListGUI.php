<?php

declare(strict_types=1);

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
 * Class ilObjLTIConsumerVerificationListGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */
class ilObjLTIConsumerVerificationListGUI extends ilObjectListGUI
{
    public function init(): void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = false;
        $this->type = "ltiv";
        $this->gui_class_name = ilObjLTIConsumerVerificationGUI::class;

        // general commands array
        $this->commands = ilObjLTIConsumerVerificationAccess::_getCommands();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getProperties(): array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        return array(
            array("alert" => false, "property" => $DIC->language()->txt("type"),
                "value" => $DIC->language()->txt("wsp_list_ltiv"))
        );
    }
}
