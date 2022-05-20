<?php

use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\MyStaff\ListCertificates\ilMStListCertificatesTableGUI;
use ILIAS\HTTP\Wrapper\WrapperFactory;

/**
 * Class ilMStListCertificatesGUI
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @ilCtrl_IsCalledBy ilMStListCertificatesGUI: ilMyStaffGUI
 * @ilCtrl_Calls      ilMStListCertificatesGUI: ilFormPropertyDispatchGUI
 * @ilCtrl_Calls      ilMStListCertificatesGUI: ilUserCertificateApiGUI
 */
class ilMStListCertificatesGUI
{
    const CMD_APPLY_FILTER = 'applyFilter';
    const CMD_INDEX = 'index';
    const CMD_GET_ACTIONS = "getActions";
    const CMD_RESET_FILTER = 'resetFilter';
    protected ilTable2GUI $table;
    protected ilMyStaffAccess $access;
    private ilGlobalTemplateInterface $main_tpl;
    private ilCtrlInterface $ctrl;
    private ilLanguage $language;
    private WrapperFactory $httpWrapper;
    private ILIAS\Refinery\Factory $refinery;
    private ilAccessHandler $accessHandler;

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->language = $DIC->language();
        $this->httpWrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        $this->access = ilMyStaffAccess::getInstance();
        $this->accessHandler = $DIC->access();
    }

    protected function checkAccessOrFail() : void
    {
        if ($this->access->hasCurrentUserAccessToMyStaff()) {
            return;
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $this->language->txt("permission_denied"), true);
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
                $this->table = new ilMStListCertificatesTableGUI($this, self::CMD_INDEX);
                $this->table->executeCommand();
                break;
            case strtolower(ilUserCertificateApiGUI::class):
                $this->checkAccessOrFail();
                $this->ctrl->forwardCommand(new ilUserCertificateApiGUI());
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
        $this->checkAccessOrFail();

        $this->table = new ilMStListCertificatesTableGUI($this, self::CMD_INDEX);
        $this->main_tpl->setTitle($this->language->txt('mst_list_certificates'));
        $this->main_tpl->setContent($this->table->getHTML());
    }

    final public function applyFilter() : void
    {
        $this->table = new ilMStListCertificatesTableGUI($this, self::CMD_APPLY_FILTER);
        $this->table->writeFilterToSession();
        $this->table->resetOffset();
        $this->index();
    }

    final public function resetFilter() : void
    {
        $this->table = new ilMStListCertificatesTableGUI($this, self::CMD_RESET_FILTER);
        $this->table->resetOffset();
        $this->table->resetFilter();
        $this->index();
    }

    final public function getId() : string
    {
        $this->table = new ilMStListCertificatesTableGUI($this, self::CMD_INDEX);

        return $this->table->getId();
    }

    final public function cancel() : void
    {
        $this->ctrl->redirect($this);
    }

    final public function getActions() : void
    {
        $mst_co_usr_id = 0;
        $mst_lco_crs_ref_id = 0;
        if ($this->httpWrapper->query()->has('mst_lco_usr_id') && $this->httpWrapper->query()->has('mst_lco_crs_ref_id')) {
            $mst_co_usr_id = $this->httpWrapper->query()->retrieve('mst_lco_usr_id', $this->refinery->kindlyTo()->int());
            $mst_lco_crs_ref_id = $this->httpWrapper->query()->retrieve('mst_lco_crs_ref_id', $this->refinery->kindlyTo()->int());
        }

        if ($mst_co_usr_id > 0 && $mst_lco_crs_ref_id > 0) {
            $selection = new ilAdvancedSelectionListGUI();

            if ($this->accessHandler->checkAccess("visible", "", $mst_lco_crs_ref_id)) {
                $link = ilLink::_getStaticLink($mst_lco_crs_ref_id, ilMyStaffAccess::DEFAULT_CONTEXT);
                $selection->addItem(
                    ilObject2::_lookupTitle(ilObject2::_lookupObjectId($mst_lco_crs_ref_id)),
                    '',
                    $link
                );
            };

            $org_units = ilOrgUnitPathStorage::getTextRepresentationOfOrgUnits('ref_id');
            /**
             * @var Array<ilOrgUnitUserAssignment> $assignments
             */
            $assignments = ilOrgUnitUserAssignment::innerjoin('object_reference', 'orgu_id', 'ref_id')->where(
                [
                'user_id' => $mst_co_usr_id,
                'object_reference.deleted' => null
                ],
                ['user_id' => '=', 'object_reference.deleted' => '!=']
            )->get();
            foreach ($assignments as $org_unit_assignment) {
                if ($this->accessHandler->checkAccess("read", "", $org_unit_assignment->getOrguId())) {
                    $link = ilLink::_getStaticLink($org_unit_assignment->getOrguId(), 'orgu');
                    $selection->addItem($org_units[$org_unit_assignment->getOrguId()], '', $link);
                }
            }

            $selection = ilMyStaffGUI::extendActionMenuWithUserActions(
                $selection,
                $mst_co_usr_id,
                rawurlencode($this->ctrl->getLinkTarget($this, self::CMD_INDEX))
            );

            echo $selection->getHTML(true);
        }
        exit;
    }
}
