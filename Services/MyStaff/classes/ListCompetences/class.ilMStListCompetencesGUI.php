<?php

use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\DI\Container;

/**
 * Class ilMStListCompetencesGUI
 *
 * @author            Martin Studer <ms@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilMStListCompetencesGUI: ilMyStaffGUI
 * @ilCtrl_Calls      ilMStListCompetencesGUI: ilMStListCompetencesSkillsGUI
 * @ilCtrl_Calls      ilMStListCompetencesGUI: ilMStListCompetencesProfilesGUI
 */
class ilMStListCompetencesGUI
{
    const CMD_APPLY_FILTER = 'applyFilter';
    const CMD_INDEX = 'index';
    const CMD_GET_ACTIONS = "getActions";
    const CMD_RESET_FILTER = 'resetFilter';
    const SUB_TAB_SKILLS = 'skills';
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
    public function __construct(Container $dic = null)
    {
        if (is_null($dic)) {
            global $DIC;
            $dic = $DIC;
        }
        $this->access = ilMyStaffAccess::getInstance();
        $this->dic = $dic;
    }


    /**
     *
     */
    protected function checkAccessOrFail()
    {
        if ($this->access->hasCurrentUserAccessToCompetences()) {
            return;
        } else {
            ilUtil::sendFailure($this->dic->language()->txt("permission_denied"), true);
            $this->dic->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }
    }


    /**
     *
     */
    public function executeCommand()
    {
        $cmd = $this->dic->ctrl()->getCmd();
        $next_class = $this->dic->ctrl()->getNextClass();
        switch ($next_class) {
            case strtolower(ilMStListCompetencesSkillsGUI::class):
                $this->addSubTabs(self::SUB_TAB_SKILLS);
                $gui = new ilMStListCompetencesSkillsGUI($this->dic);
                $this->dic->ctrl()->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_INDEX:
                        $this->$cmd();
                        break;
                    default:
                        $this->index();
                        break;
                }
                break;
        }
    }


    /**
     * @param string $subtab_active
     */
    protected function addSubTabs(string $subtab_active) : void
    {
        $this->dic->language()->loadLanguageModule('skmg');
        $this->dic->tabs()->addSubTab(
            self::SUB_TAB_SKILLS,
            $this->dic->language()->txt('skmg_selected_skills'),
            $this->dic->ctrl()->getLinkTargetByClass([
                self::class,
                ilMStListCompetencesSkillsGUI::class
            ])
        );

        $this->dic->tabs()->activateSubTab($subtab_active);
    }


    /**
     *
     */
    public function index()
    {
        $this->dic->ctrl()->redirectByClass(ilMStListCompetencesSkillsGUI::class);
    }
}
