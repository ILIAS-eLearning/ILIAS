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

use ILIAS\DI\Container;
use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\MyStaff\ListCompetences\Skills\ilMStListCompetencesSkillsTableGUI;

/**
 * Class ilMStListCompetencesSkillsGUI
 * @package ILIAS\MyStaff\ListCompetences
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilMStListCompetencesSkillsGUI
{
    public const CMD_APPLY_FILTER = 'applyFilter';
    public const CMD_INDEX = 'index';
    public const CMD_GET_ACTIONS = "getActions";
    public const CMD_RESET_FILTER = 'resetFilter';
    protected ilTable2GUI $table;
    protected ilMyStaffAccess $access;
    private Container $dic;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(Container $dic)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->access = ilMyStaffAccess::getInstance();
        $this->dic = $dic;
    }

    protected function checkAccessOrFail(): void
    {
        if ($this->access->hasCurrentUserAccessToCompetences()) {
            return;
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $this->dic->language()->txt("permission_denied"), true);
            $this->dic->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }
    }

    final public function executeCommand(): void
    {
        $cmd = $this->dic->ctrl()->getCmd();
        $next_class = $this->dic->ctrl()->getNextClass();
        switch ($next_class) {
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
                break;
        }
    }

    final public function index(): void
    {
        $this->listUsers();
    }

    final public function listUsers(): void
    {
        $this->checkAccessOrFail();

        $this->table = new ilMStListCompetencesSkillsTableGUI($this, self::CMD_INDEX, $this->dic);
        $this->dic->ui()->mainTemplate()->setTitle($this->dic->language()->txt('mst_list_competences'));
        $this->dic->ui()->mainTemplate()->setTitleIcon(ilUtil::getImagePath('icon_skmg.svg'));
        $this->dic->ui()->mainTemplate()->setContent($this->table->getHTML());
    }

    final public function applyFilter(): void
    {
        $this->table = new ilMStListCompetencesSkillsTableGUI($this, self::CMD_APPLY_FILTER, $this->dic);
        $this->table->writeFilterToSession();
        $this->table->resetOffset();
        $this->index();
    }

    final public function resetFilter(): void
    {
        $this->table = new ilMStListCompetencesSkillsTableGUI($this, self::CMD_RESET_FILTER, $this->dic);
        $this->table->resetOffset();
        $this->table->resetFilter();
        $this->index();
    }

    final public function getId(): string
    {
        $this->table = new ilMStListCompetencesSkillsTableGUI($this, self::CMD_INDEX, $this->dic);

        return $this->table->getId();
    }

    final public function cancel(): void
    {
        $this->dic->ctrl()->redirect($this);
    }
}
