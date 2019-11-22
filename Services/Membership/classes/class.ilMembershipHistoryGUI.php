<?php

use ILIAS\DI\Container;
use ILIAS\Membership\Changelog\UI\MembershipHistoryTableGUI;

/**
 * Class ilMembershipHistoryGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilMembershipHistoryGUI: ilCourseMembershipGUI
 */
class ilMembershipHistoryGUI
{

    const CMD_STANDARD = 'show';
    const CMD_APPLY_FILTER = 'applyFilter';
    const CMD_RESET_FILTER = 'resetFilter';

    /**
     * @var int
     */
    protected $course_object_id;
    /**
     * @var ilMembershipGUI
     */
    protected $parent_gui;
    /**
     * @var Container
     */
    protected $dic;


    /**
     * ilMembershipHistoryGUI constructor.
     *
     * @param           $parent_gui
     * @param Container $dic
     * @param int       $course_object_id
     */
    public function __construct($parent_gui, Container $dic, int $course_object_id)
    {
        $this->course_object_id = $course_object_id;
        $this->parent_gui = $parent_gui;
        $this->dic = $dic;
    }


    /**
     *
     */
    public function executeCommand() : void
    {
        $cmd = $this->dic->ctrl()->getCmd(self::CMD_STANDARD);
        $this->$cmd();
    }


    /**
     * @return MembershipHistoryTableGUI
     */
    protected function buildTable() : MembershipHistoryTableGUI
    {
        return new MembershipHistoryCourseTableGUI(
            $this,
            $this->dic,
            new TableOptions('mem_history_' . $this->course_object_id),
            $this->course_object_id
        );
    }

    /**
     *
     */
    protected function show() : void
    {
        $table = $this->buildTable();
        $this->dic->ui()->mainTemplate()->setContent($table->getHTML());
    }


    /**
     *
     */
    protected function applyFilter() : void
    {
        $table = $this->buildTable();
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->show();
    }


    /**
     *
     */
    protected function resetFilter() : void
    {
        $table = $this->buildTable();
        $table->resetOffset();
        $table->resetFilter();
        $this->show();
    }
}