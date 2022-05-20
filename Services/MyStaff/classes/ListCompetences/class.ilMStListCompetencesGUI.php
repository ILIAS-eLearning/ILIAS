<?php

use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\DI\Container;

/**
 * Class ilMStListCompetencesGUI
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @ilCtrl_IsCalledBy ilMStListCompetencesGUI: ilMyStaffGUI
 * @ilCtrl_Calls      ilMStListCompetencesGUI: ilMStListCompetencesSkillsGUI
 * @ilCtrl_Calls      ilMStListCompetencesGUI: ilMStListCompetencesProfilesGUI
 */
class ilMStListCompetencesGUI
{
    public const CMD_APPLY_FILTER = 'applyFilter';
    public const CMD_INDEX = 'index';
    public const CMD_GET_ACTIONS = "getActions";
    public const CMD_RESET_FILTER = 'resetFilter';
    public const SUB_TAB_SKILLS = 'skills';
    protected ilTable2GUI $table;
    protected ilMyStaffAccess $access;
    private Container $dic;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(Container $dic)
    {
        $this->main_tpl = $dic->ui()->mainTemplate();
        $this->access = ilMyStaffAccess::getInstance();
        $this->dic = $dic;
    }

    protected function checkAccessOrFail() : void
    {
        if ($this->access->hasCurrentUserAccessToMyStaff()) {
            return;
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $this->dic->language()->txt("permission_denied"), true);
            $this->dic->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }
    }

    final public function executeCommand() : void
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

    final public function index() : void
    {
        $this->dic->ctrl()->redirectByClass(ilMStListCompetencesSkillsGUI::class);
    }

    final public function getActions() : void
    {
        $mst_co_usr_id = $this->dic->http()->request()->getQueryParams()['mst_lco_usr_id'];
        $mst_lco_crs_ref_id = $this->dic->http()->request()->getQueryParams()['mst_lco_crs_ref_id'];

        if ($mst_co_usr_id > 0 && $mst_lco_crs_ref_id > 0) {
            $selection = new ilAdvancedSelectionListGUI();

            if ($this->dic->access()->checkAccess("visible", "", $mst_lco_crs_ref_id)) {
                $link = ilLink::_getStaticLink($mst_lco_crs_ref_id, ilMyStaffAccess::DEFAULT_CONTEXT);
                $selection->addItem(
                    ilObject2::_lookupTitle(ilObject2::_lookupObjectId($mst_lco_crs_ref_id)),
                    '',
                    $link
                );
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

            $selection = ilMyStaffGUI::extendActionMenuWithUserActions(
                $selection,
                $mst_co_usr_id,
                rawurlencode($this->dic->ctrl()
                                       ->getLinkTarget($this, self::CMD_INDEX))
            );

            echo $selection->getHTML(true);
        }
        exit;
    }
}
