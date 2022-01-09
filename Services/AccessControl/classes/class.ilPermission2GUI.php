<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * Class ilPermissionGUI
 * RBAC related output
 * @author     Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup    ServicesAccessControl
 */
class ilPermission2GUI
{
    private const TAB_POSITION_PERMISSION_SETTINGS = "position_permission_settings";

    protected object $gui_obj;
    protected ilErrorHandling $ilErr;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilObjectDefinition $objDefinition;
    protected ilGlobalTemplateInterface $tpl;
    protected ilRbacSystem $rbacsystem;
    protected ilRbacReview $rbacreview;
    protected ilRbacAdmin $rbacadmin;
    protected ilObjectDataCache $objectDataCache;
    protected ilTabsGUI $tabs;
    protected GlobalHttpState $http;
    protected Factory $refinery;

    private array $roles = [];
    private int $num_roles = 0;

    public function __construct(object $a_gui_obj)
    {
        global $DIC;

        $this->objDefinition = $DIC['objDefinition'];
        $this->objectDataCache = $DIC['ilObjDataCache'];
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("rbac");
        $this->ctrl = $DIC->ctrl();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->rbacreview = $DIC->rbac()->review();
        $this->rbacadmin = $DIC->rbac()->admin();
        $this->tabs = $DIC->tabs();
        $this->ilErr = $DIC['ilErr'];
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->gui_obj = $a_gui_obj;
    }

    // show owner sub tab
    public function owner() : void
    {
        $this->__initSubTabs("owner");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "owner"));
        $form->setTitle($this->lng->txt("info_owner_of_object"));

        $login = new ilTextInputGUI($this->lng->txt("login"), "owner");
        $login->setDataSource($this->ctrl->getLinkTargetByClass(array(get_class($this),
                                                                      'ilRepositorySearchGUI'
        ), 'doUserAutoComplete', '', true));
        $login->setRequired(true);
        $login->setSize(50);
        $login->setInfo($this->lng->txt("chown_warning"));
        $login->setValue(ilObjUser::_lookupLogin($this->gui_obj->object->getOwner()));
        $form->addItem($login);
        $form->addCommandButton("changeOwner", $this->lng->txt("change_owner"));
        $this->tpl->setContent($form->getHTML());
    }

    public function changeOwner() : void
    {
        $owner = '';
        if ($this->http->wrapper()->post()->has('owner')) {
            $owner = $this->http->wrapper()->post()->retrieve(
                'owner',
                $this->refinery->kindlyTo()->string()
            );
        }
        if (!$user_id = ilObjUser::_lookupId($owner)) {
            ilUtil::sendFailure($this->lng->txt('user_not_known'));
            $this->owner();
            return;
        }

        // no need to change?
        if ($user_id != $this->gui_obj->object->getOwner()) {
            $this->gui_obj->object->setOwner($user_id);
            $this->gui_obj->object->updateOwner();
            $this->objectDataCache->deleteCachedEntry($this->gui_obj->object->getId());

            if (ilRbacLog::isActive()) {
                ilRbacLog::add(ilRbacLog::CHANGE_OWNER, $this->gui_obj->object->getRefId(), array($user_id));
            }
        }

        ilUtil::sendSuccess($this->lng->txt('owner_updated'), true);

        if (!$this->rbacsystem->checkAccess("edit_permission", $this->gui_obj->object->getRefId())) {
            $this->ctrl->redirect($this->gui_obj);
            return;
        }
        $this->ctrl->redirect($this, 'owner');
    }

    // init sub tabs
    public function __initSubTabs(string $a_cmd) : void
    {
        $perm = $a_cmd == 'perm';
        $perm_positions = $a_cmd == ilPermissionGUI::CMD_PERM_POSITIONS;
        $info = $a_cmd == 'perminfo';
        $owner = $a_cmd == 'owner';
        $log = $a_cmd == 'log';

        $this->tabs->addSubTabTarget(
            "permission_settings",
            $this->ctrl->getLinkTarget($this, "perm"),
            "",
            "",
            "",
            $perm
        );

        if (ilOrgUnitGlobalSettings::getInstance()->isPositionAccessActiveForObject($this->gui_obj->object->getId())) {
            $this->tabs->addSubTabTarget(self::TAB_POSITION_PERMISSION_SETTINGS,
                $this->ctrl->getLinkTarget($this, ilPermissionGUI::CMD_PERM_POSITIONS), "", "", "", $perm_positions);
        }

        $this->tabs->addSubTabTarget(
            "info_status_info",
            $this->ctrl->getLinkTargetByClass(array(get_class($this), "ilobjectpermissionstatusgui"), "perminfo"),
            "",
            "",
            "",
            $info
        );
        $this->tabs->addSubTabTarget(
            "owner",
            $this->ctrl->getLinkTarget($this, "owner"),
            "",
            "",
            "",
            $owner
        );

        if (ilRbacLog::isActive()) {
            $this->tabs->addSubTabTarget(
                "rbac_log",
                $this->ctrl->getLinkTarget($this, "log"),
                "",
                "",
                "",
                $log
            );
        }
    }

    public function log() : void
    {
        if (!ilRbacLog::isActive()) {
            $this->ctrl->redirect($this, "perm");
        }

        $this->__initSubTabs("log");

        $table = new ilRbacLogTableGUI($this, "log", $this->gui_obj->object->getRefId());
        $this->tpl->setContent($table->getHTML());
    }

    public function applyLogFilter() : void
    {
        $table = new ilRbacLogTableGUI($this, "log", $this->gui_obj->object->getRefId());
        $table->resetOffset();
        $table->writeFilterToSession();
        $this->log();
    }

    public function resetLogFilter() : void
    {
        $table = new ilRbacLogTableGUI($this, "log", $this->gui_obj->object->getRefId());
        $table->resetOffset();
        $table->resetFilter();
        $this->log();
    }
}
