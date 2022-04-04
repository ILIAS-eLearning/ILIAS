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
        $a_type,
        $a_ref_id,
        $a_obj_id,
        $a_header_actions,
        $a_check_write_access = true
    ) : bool {
        return $this->comments_enabled;
    }

    /**
     * Comments cannot be enabled.
     * @param bool $a_value
     * @param bool $a_enable_comments_settings
     */
    public function enableComments($a_value, $a_enable_comments_settings = true) : void
    {
    }

    /**
     * @param bool $a_value
     */
    public function enableNotes($a_value) : void
    {
    }

    /**
     * @param bool $a_value
     */
    public function enableTags($a_value) : void
    {
    }
}
