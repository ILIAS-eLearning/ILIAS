<?php

use ILIAS\DI\Container;
use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\MyStaff\ListCompetences\Skills\ilMStListCompetencesSkillsTableGUI;

/**
 * Class ilMStListCompetencesSkillsGUI
 *
 * @package ILIAS\MyStaff\ListCompetences
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilMStListCompetencesSkillsGUI
{
    const CMD_APPLY_FILTER = 'applyFilter';
    const CMD_INDEX = 'index';
    const CMD_GET_ACTIONS = "getActions";
    const CMD_RESET_FILTER = 'resetFilter';
    /**
     * @var ilTable2GUI
     */
    protected $table;
    /**
     * @var ilMyStaffAccess
     */
    protected $access;
    /**
     * @var Container
     */
    private $dic;


    /**
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->access = ilMyStaffAccess::getInstance();
        $this->dic = $dic;
    }


    protected function checkAccessOrFail() : void
    {
        if ($this->access->hasCurrentUserAccessToCompetences()) {
            return;
        } else {
            ilUtil::sendFailure($this->dic->language()->txt("permission_denied"), true);
            $this->dic->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }
    }


    public function executeCommand() : void
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


    public function index() : void
    {
        $this->listUsers();
    }


    public function listUsers() : void
    {
        $this->checkAccessOrFail();

        $this->table = new ilMStListCompetencesSkillsTableGUI($this, self::CMD_INDEX, $this->dic);
        $this->dic->ui()->mainTemplate()->setTitle($this->dic->language()->txt('mst_list_competences'));
        $this->dic->ui()->mainTemplate()->setTitleIcon(ilUtil::getImagePath('icon_skmg.svg'));
        $this->dic->ui()->mainTemplate()->setContent($this->table->getHTML());
    }


    public function applyFilter() : void
    {
        $this->table = new ilMStListCompetencesSkillsTableGUI($this, self::CMD_APPLY_FILTER, $this->dic);
        $this->table->writeFilterToSession();
        $this->table->resetOffset();
        $this->index();
    }


    public function resetFilter() : void
    {
        $this->table = new ilMStListCompetencesSkillsTableGUI($this, self::CMD_RESET_FILTER, $this->dic);
        $this->table->resetOffset();
        $this->table->resetFilter();
        $this->index();
    }


    /**
     * @return string
     */
    public function getId() : string
    {
        $this->table = new ilMStListCompetencesSkillsTableGUI($this, self::CMD_INDEX, $this->dic);

        return $this->table->getId();
    }


    public function cancel() : void
    {
        $this->dic->ctrl()->redirect($this);
    }
}
