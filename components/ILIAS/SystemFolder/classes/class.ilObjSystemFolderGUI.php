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
 *********************************************************************/

/**
 * Class ilObjSystemFolderGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @ilCtrl_Calls ilObjSystemFolderGUI: ilPermissionGUI
 */
class ilObjSystemFolderGUI extends ilObjectGUI
{
    protected \Pimple\Container $dic;
    protected \ILIAS\Repository\InternalGUIService $gui;
    protected ilPropertyFormGUI $form;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;
    protected ilTabsGUI $tabs;
    protected ilRbacSystem $rbacsystem;
    protected ilObjectDefinition $obj_definition;
    protected ilErrorHandling $error;
    protected ilDBInterface $db;
    protected ilStyleDefinition $style_definition;
    protected ilHelpGUI $help;
    protected ilIniFile $client_ini;
    public string $type;
    protected \ILIAS\HTTP\Wrapper\WrapperFactory $wrapper;
    protected \ILIAS\Refinery\Factory $refinery;

    /**
    * Constructor
    * @access public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->user = $DIC->user();
        $this->obj_definition = $DIC["objDefinition"];
        $this->settings = $DIC->settings();
        $this->error = $DIC["ilErr"];
        $this->db = $DIC->database();
        $this->style_definition = $DIC["styleDefinition"];
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->help = $DIC["ilHelp"];
        $this->toolbar = $DIC->toolbar();
        $this->client_ini = $DIC["ilClientIniFile"];
        $this->type = "adm";
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->lng->loadLanguageModule("administration");
        $this->lng->loadLanguageModule("adm");
        $this->content_style_domain = $DIC->contentStyle()
                  ->domain()
                  ->styleForRefId($this->object->getRefId());
        $this->gui = $DIC->repository()->internal()->gui();
    }

    public function executeCommand(): void
    {
        $ilTabs = $this->tabs;

        $next_class = $this->ctrl->getNextClass($this);
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilobjectownershipmanagementgui":
                $this->setSystemCheckSubTabs("no_owner");
                $gui = $this->gui->ownership()->ownershipManagementGUI(0);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $cmd = $this->ctrl->getCmd("view");

                $cmd .= "Object";
                $this->$cmd();

                break;
        }
    }

    /**
    * show admin subpanels and basic settings form
    *
    * @access	public
    */
    public function viewObject(): void
    {
        $this->ctrl->redirectByClass(ilPermissionGUI::class, 'perm');
    }

    /**
    * Set sub tabs for general settings
    */
    public function setSystemCheckSubTabs($a_activate): void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $ilTabs->addSubTab(
            "system_check_sub",
            $this->lng->txt("system_check"),
            $ilCtrl->getLinkTarget($this, "check")
        );
        $ilTabs->addSubTab(
            "no_owner",
            $this->lng->txt("system_check_no_owner"),
            $ilCtrl->getLinkTargetByClass("ilObjectOwnershipManagementGUI")
        );

        $ilTabs->setSubTabActive($a_activate);
        $ilTabs->setTabActive("system_check");
    }

    public function cancelObject(): void
    {
        $this->ctrl->redirect($this, "view");
    }

    // get tabs
    public function getAdminTabs(): void
    {
        $rbacsystem = $this->rbacsystem;
        $ilHelp = $this->help;

        //		$ilHelp->setScreenIdComponent($this->object->getType());

        $this->ctrl->setParameter($this, "ref_id", $this->object->getRefId());

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }

    /**
     * goto target group
     */
    public static function _goto(): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();

        $a_target = SYSTEM_FOLDER_ID;

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            ilUtil::redirect("ilias.php?baseClass=ilAdministrationGUI");
            exit;
        } else {
            if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
                $main_tpl->setOnScreenMessage('failure', sprintf(
                    $lng->txt("msg_no_perm_read_item"),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                ), true);
                ilObjectGUI::_gotoRepositoryRoot();
            }
        }
        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }
}
