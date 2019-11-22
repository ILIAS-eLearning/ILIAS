<?php

use ILIAS\Membership\Changelog\ChangelogService;
use ILIAS\Membership\Changelog\Infrastructure\Repository\ilDBEventRepository;
use ILIAS\Membership\Changelog\UI\MembershipTableGUI;

/**
 * Class MembershipHistoryUserTableGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class MembershipHistoryUserTableGUI extends MembershipTableGUI
{

    const ROW_TEMPLATE = './Services/Membership/templates/default/tpl.mem_history_usr_row';

    /**
     * @return mixed
     */
    protected function initColumns() : void
    {
        $this->addColumn($this->dic->language()->txt('col_event_name'), 'event_name');
        $this->addColumn($this->dic->language()->txt('col_object'), 'object');
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


    /**
     *
     */
    protected function initData() : void
    {
        $changelog_service = new ChangelogService(new ilDBEventRepository());
        $data = $changelog_service->query(
            $changelog_service->queryFactory()->filter(),
            $changelog_service->queryFactory()->options()
        );
    }
}