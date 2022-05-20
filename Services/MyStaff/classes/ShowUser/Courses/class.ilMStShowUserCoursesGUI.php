<?php

use ILIAS\MyStaff\Courses\ShowUser\ilMStShowUserCoursesTableGUI;
use ILIAS\MyStaff\ilMyStaffAccess;

/**
 * Class ilMStShowUserCoursesGUI
 * @package           ILIAS\MyStaff\Courses\ShowUser
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 * @ilCtrl_IsCalledBy ilMStShowUserCoursesGUI: ilMStShowUserGUI
 * @ilCtrl_Calls      ilMStShowUserCoursesGUI: ilFormPropertyDispatchGUI
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

        if ($this->access->hasCurrentUserAccessToMyStaff()
            && $this->access->hasCurrentUserAccessToUser($this->usr_id)
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
            case strtolower(ilFormPropertyDispatchGUI::class):
                $DIC->ctrl()->setReturn($this, self::CMD_INDEX);
                $this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_INDEX);
                $this->table->executeCommand();
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

    protected function index() : void
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

    protected function applyFilter() : void
    {
        $this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_APPLY_FILTER);
        $this->table->writeFilterToSession();
        $this->table->resetOffset();
        $this->index();
    }

    protected function resetFilter() : void
    {
        $this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_RESET_FILTER);
        $this->table->resetOffset();
        $this->table->resetFilter();
        $this->index();
    }

    final public function getId() : string
    {
        $this->table = new ilMStShowUserCoursesTableGUI($this, self::CMD_INDEX);

        return $this->table->getId();
    }

    final public function cancel() : void
    {
        global $DIC;
        $DIC->ctrl()->redirect($this);
    }

    final public function getActions() : void
    {
        global $DIC;

        $mst_co_usr_id = $DIC->http()->request()->getQueryParams()['mst_lco_usr_id'];
        $mst_lco_crs_ref_id = $DIC->http()->request()->getQueryParams()['mst_lco_crs_ref_id'];

        if ($mst_co_usr_id > 0 && $mst_lco_crs_ref_id > 0) {
            $selection = new ilAdvancedSelectionListGUI();

            if ($DIC->access()->checkAccess("visible", "", $mst_lco_crs_ref_id)) {
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
                if ($DIC->access()->checkAccess("read", "", $org_unit_assignment->getOrguId())) {
                    $link = ilLink::_getStaticLink($org_unit_assignment->getOrguId(), 'orgu');
                    $selection->addItem($org_units[$org_unit_assignment->getOrguId()], '', $link);
                }
            }

            $selection = ilMyStaffGUI::extendActionMenuWithUserActions(
                $selection,
                $mst_co_usr_id,
                rawurlencode($DIC->ctrl()
                                 ->getLinkTarget($this, self::CMD_INDEX))
            );

            echo $selection->getHTML(true);
        }
        exit;
    }
}
