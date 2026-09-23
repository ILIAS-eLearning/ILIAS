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
 * Presentation of an LTI object in the listing of its parent container.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilObjLTIConsumerListGUI extends ilObjectListGUI
{
    public function init(): void
    {
        $this->type = 'lti';
        $this->gui_class_name = ilObjLTIConsumerGUI::class;
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->link_enabled = true;
        $this->subscribe_enabled = false;
        $this->progress_enabled = true;
        $this->notice_properties_enabled = true;
        $this->info_screen_enabled = true;
        $this->commands = ilObjLTIConsumerAccess::_getCommands();
    }
}
