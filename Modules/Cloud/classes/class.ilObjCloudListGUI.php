<?php declare(strict_types=0);
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

class ilObjCloudListGUI extends ilObjectListGUI
{
    public function init() : void
    {
        // Only delete remains possible
        $this->delete_enabled = true;

        $this->static_link_enabled = false;
        $this->cut_enabled = false;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->copy_enabled = false;
        $this->progress_enabled = false;
        $this->info_screen_enabled = false;
        $this->tags_enabled = false;
        $this->comments_enabled = false;
        $this->notes_enabled = false;
        $this->timings_enabled = false;
        $this->notice_properties_enabled = false;
        $this->type = 'cld';
        $this->gui_class_name = 'ilObjCloudGUI';
    }

    public function getProperties() : array
    {
        return [[
            'alert' => true,
            'property' => $this->lng->txt('status'),
            'value' => $this->lng->txt('cld_abandoned'),
        ]];
    }
}
