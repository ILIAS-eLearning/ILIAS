<?php

use ILIAS\Membership\Changelog\UI\MembershipTableGUI;

/**
 * Class ChangelogTableGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipHistoryCourseTableGUI extends MembershipTableGUI
{

    const ROW_TEMPLATE = './Services/Membership/templates/default/tpl.mem_history_crs_row';


    /**
     *
     */
    protected function initColumns() : void
    {
        $this->addColumn($this->dic->language()->txt('col_event_name'), 'event_name');
        $this->addColumn($this->dic->language()->txt('col_member'), 'member');
        $this->addColumn($this->dic->language()->txt('col_actor'), 'actor');
        $this->addColumn($this->dic->language()->txt('col_occurred_at'), 'timestamp');
    }


    /**
     * @return string
     */
    protected function getRowTemplatePath() : string
    {
        return self::ROW_TEMPLATE;
    }
}