<?php

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

    /**
     * @param string $a_type
     * @param int    $a_ref_id
     * @param int    $a_obj_id
     * @param bool   $a_header_actions
     * @param bool   $a_check_write_access
     * @return bool
     */
    protected function isCommentsActivated(
        string $a_type,
        int $a_ref_id,
        int $a_obj_id,
        bool $a_header_actions,
        bool $a_check_write_access = true
    ): bool {
        return $this->comments_enabled;
    }

    /**
     * Comments cannot be enabled.
     * @param bool $a_value
     * @param bool $a_enable_comments_settings
     * @return bool
     */
    public function enableComments(bool $a_value, bool $a_enable_comments_settings = true): void
    {

    }

    /**
     * @param bool $a_value
     * @return bool
     */
    public function enableNotes(bool $a_value): void
    {

    }

    /**
     * @param bool $a_value
     * @return bool
     */
    public function enableTags(bool $a_value): void
    {

    }
}
