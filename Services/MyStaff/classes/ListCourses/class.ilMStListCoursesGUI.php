<?php
declare(strict_types=1);

use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\MyStaff\ListCourses\ilMStListCoursesTableGUI;
use ILIAS\HTTP\Wrapper\WrapperFactory;

/**
 * Class ilMStListCoursesGUI
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @ilCtrl_IsCalledBy ilMStListCoursesGUI: ilMyStaffGUI
 * @ilCtrl_Calls      ilMStListCoursesGUI: ilFormPropertyDispatchGUI
 */
class ilMStListCoursesGUI extends ilPropertyFormGUI
{
    public const CMD_APPLY_FILTER = 'applyFilter';
    public const CMD_INDEX = 'index';
    public const CMD_GET_ACTIONS = "getActions";
    public const CMD_RESET_FILTER = 'resetFilter';
    protected ilTable2GUI $table;
    protected ilMyStaffAccess $access;
    private \ilGlobalTemplateInterface $main_tpl;
    private \ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper $queryWrapper;
    private ilHelpGUI $help;

    public function __construct()
    {
        global $DIC;
        parent::__construct();
        
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->access = ilMyStaffAccess::getInstance();
        $this->help = $DIC->help();
        $this->queryWrapper = $DIC->http()->wrapper()->query();
        $this->help->setScreenIdComponent('msta');
    }

    protected function checkAccessOrFail() : void
    {
        if ($this->access->hasCurrentUserAccessToMyStaff()) {
            return;
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilDashboardGUI::class, "");
        }
    }

    final public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case strtolower(ilFormPropertyDispatchGUI::class):
                $this->checkAccessOrFail();

                $this->ctrl->setReturn($this, self::CMD_INDEX);
                $this->table = new ilMStListCoursesTableGUI($this, self::CMD_INDEX);
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
                break;
        }
    }

    final public function index() : void
    {
        $this->listUsers();
    }

    final public function listUsers() : void
    {
        global $DIC;

        $this->checkAccessOrFail();
        $this->help->setScreenId('courses_list');

        $this->table = new ilMStListCoursesTableGUI($this, self::CMD_INDEX);
        $DIC->ui()->mainTemplate()->setTitle($DIC->language()->txt('mst_list_courses'));
        $DIC->ui()->mainTemplate()->setContent($this->table->getHTML());
    }

    final public function applyFilter() : void
    {
        $this->table = new ilMStListCoursesTableGUI($this, self::CMD_APPLY_FILTER);
        $this->table->writeFilterToSession();
        $this->table->resetOffset();
        $this->index();
    }

    final public function resetFilter() : void
    {
        $this->table = new ilMStListCoursesTableGUI($this, self::CMD_RESET_FILTER);
        $this->table->resetOffset();
        $this->table->resetFilter();
        $this->index();
    }

    final public function getId() : string
    {
        $this->table = new ilMStListCoursesTableGUI($this, self::CMD_INDEX);

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

        $mst_co_usr_id = 0;
        $mst_lco_crs_ref_id = 0;
        if ($this->queryWrapper->has('mst_lco_usr_id')) {
            $mst_co_usr_id = $this->queryWrapper->retrieve('mst_lco_usr_id', $this->refinery->kindlyTo()->int());
        }
        
        if ($this->queryWrapper->has('mst_lco_crs_ref_id')) {
            $mst_lco_crs_ref_id = $this->queryWrapper->retrieve('mst_lco_crs_ref_id', $this->refinery->kindlyTo()->int());
        }

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

            $org_units = ilOrgUnitPathStorage::getTextRepresentationOfOrgUnits(true);
            foreach (ilOrgUnitUserAssignment::innerjoin('object_reference', 'orgu_id', 'ref_id')->where(array(
                'user_id' => $mst_co_usr_id,
                'object_reference.deleted' => null
            ), array('user_id' => '=', 'object_reference.deleted' => '!='))->get() as $org_unit_assignment) {
                if ($DIC->access()->checkAccess("read", "", $org_unit_assignment->getOrguId())) {
                    $link = ilLink::_getStaticLink($org_unit_assignment->getOrguId(), 'orgu');
                    $selection->addItem($org_units[$org_unit_assignment->getOrguId()], '', $link);
                }
            }

            $selection = ilMyStaffGUI::extendActionMenuWithUserActions(
                $selection,
                $mst_co_usr_id,
                rawurlencode($DIC->ctrl()->getLinkTarget($this, self::CMD_INDEX))
            );

            echo $selection->getHTML(true);
        }
        exit;
    }
}
