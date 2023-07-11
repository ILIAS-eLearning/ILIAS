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

use ILIAS\MyStaff\ilMyStaffAccess;

/**
 * Class ilMStShowUserCoursesGUI
 * @package           ILIAS\MyStaff\Courses\ShowUser
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 * @ilCtrl_IsCalledBy ilMStShowUserCoursesGUI: ilMStShowUserGUI
 * @ilCtrl_Calls      ilMStShowUserCoursesGUI: ilMStShowUserCoursesTableGUI
 */
class ilMStShowUserCoursesGUI
{
    public const CMD_INDEX = 'index';
    public const CMD_RESET_FILTER = 'resetFilter';
    public const CMD_APPLY_FILTER = 'applyFilter';
    public const CMD_GET_ACTIONS = "getActions";
    protected int $usr_id;
    protected ilTable2GUI $table;
    protected ilMyStaffAccess $access;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->access = ilMyStaffAccess::getInstance();

        $this->usr_id = $DIC->http()->request()->getQueryParams()['usr_id'];
        $DIC->ctrl()->setParameter($this, 'usr_id', $this->usr_id);
    }

    protected function checkAccessOrFail()
    {
        global $DIC;

        if (!$this->usr_id) {
            $this->main_tpl->setOnScreenMessage('failure', $DIC->language()->txt("permission_denied"), true);
            $DIC->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }

        if ($this->access->hasCurrentUserAccessToUser($this->usr_id)
            && $this->access->hasCurrentUserAccessToCourseMemberships()
        ) {
            return;
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $DIC->language()->txt("permission_denied"), true);
            $DIC->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }
    }

    final public function executeCommand()
    {
        global $DIC;

        $this->checkAccessOrFail();

        $cmd = $DIC->ctrl()->getCmd();
        $next_class = $DIC->ctrl()->getNextClass();

        switch ($next_class) {
            case strtolower(ilMStShowUserCoursesTableGUI::class):
                $DIC->ctrl()->setReturn($this, self::CMD_INDEX);
                $this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_INDEX);
                $DIC->ctrl()->forwardCommand($this->table);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_RESET_FILTER:
                    case self::CMD_APPLY_FILTER:
                    case self::CMD_INDEX:
                    case self::CMD_GET_ACTIONS:
                        $this->$cmd();
                        break;
                    default:
                        $this->index();
                        break;
                }
        }
    }

    protected function index(): void
    {
        $this->listUsers();
    }

    protected function listUsers()
    {
        global $DIC;

        $this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_INDEX);
        $this->table->setTitle(
            sprintf($DIC->language()->txt('mst_courses_of'), ilObjCourse::_lookupTitle($this->usr_id))
        );

        $DIC->ui()->mainTemplate()->setContent($this->table->getHTML());
    }

    protected function applyFilter(): void
    {
        $this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_APPLY_FILTER);
        $this->table->writeFilterToSession();
        $this->table->resetOffset();
        $this->index();
    }

    protected function resetFilter(): void
    {
        $this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_RESET_FILTER);
        $this->table->resetOffset();
        $this->table->resetFilter();
        $this->index();
    }

    final public function getId(): string
    {
        $this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_INDEX);

        return $this->table->getId();
    }

    final public function cancel(): void
    {
        global $DIC;
        $DIC->ctrl()->redirect($this);
    }
}
