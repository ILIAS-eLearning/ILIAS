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
 ********************************************************************
 */

/**
 * Class ilOrgUnitExtensionListGUI
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class ilOrgUnitExtensionListGUI extends ilObjectPluginListGUI
{
    protected function initListActions() : void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->comments_enabled = false;
        $this->notes_enabled = false;
        $this->tags_enabled = false;
        $this->timings_enabled = false;
    }

    protected function isCommentsActivated(
        string $type,
        int $ref_id,
        int $obj_id,
        bool $header_actions,
        bool $check_write_access = true
    ): bool {
        return $this->comments_enabled;
    }

    /**
     * Comments cannot be enabled.
     */
    public function enableComments(bool $value, bool $enable_comments_settings = true): void
    {

    }

    public function enableNotes(bool $value): void
    {

    }

    public function enableTags(bool $value): void
    {

    }
}
