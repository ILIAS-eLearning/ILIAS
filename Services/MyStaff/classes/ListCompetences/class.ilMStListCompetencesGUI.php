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


    /**
     *
     */
    public function getActions()
    {
        $mst_co_usr_id = $this->dic->http()->request()->getQueryParams()['mst_lco_usr_id'];
        $mst_lco_crs_ref_id = $this->dic->http()->request()->getQueryParams()['mst_lco_crs_ref_id'];

        if ($mst_co_usr_id > 0 && $mst_lco_crs_ref_id > 0) {
            $selection = new ilAdvancedSelectionListGUI();

            if ($this->dic->access()->checkAccess("visible", "", $mst_lco_crs_ref_id)) {
                $link = ilLink::_getStaticLink($mst_lco_crs_ref_id, ilMyStaffAccess::COURSE_CONTEXT);
                $selection->addItem(ilObject2::_lookupTitle(ilObject2::_lookupObjectId($mst_lco_crs_ref_id)), '', $link);
            };

            $org_units = ilOrgUnitPathStorage::getTextRepresentationOfOrgUnits('ref_id');
            foreach (
                ilOrgUnitUserAssignment::innerjoin('object_reference', 'orgu_id', 'ref_id')->where(array(
                    'user_id' => $mst_co_usr_id,
                    'object_reference.deleted' => null
                ), array('user_id' => '=', 'object_reference.deleted' => '!='))->get() as $org_unit_assignment
            ) {
                if ($this->dic->access()->checkAccess("read", "", $org_unit_assignment->getOrguId())) {
                    $link = ilLink::_getStaticLink($org_unit_assignment->getOrguId(), 'orgu');
                    $selection->addItem($org_units[$org_unit_assignment->getOrguId()], '', $link);
                }
            }

            $selection = ilMyStaffGUI::extendActionMenuWithUserActions($selection, $mst_co_usr_id, rawurlencode($this->dic->ctrl()
                ->getLinkTarget($this, self::CMD_INDEX)));

            echo $selection->getHTML(true);
        }
        exit;
    }
}
