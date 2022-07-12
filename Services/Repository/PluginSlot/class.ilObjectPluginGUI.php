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

use ILIAS\Repository\PluginSlot\PluginSlotGUIRequest;

/**
 * Object GUI class for repository plugins
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilObjectPluginGUI extends ilObject2GUI
{
    protected ilComponentRepository $component_repository;
    protected ilNavigationHistory $nav_history;
    protected ilTabsGUI $tabs;
    protected ilPlugin $plugin;
    protected PluginSlotGUIRequest $slot_request;
    protected ilComponentFactory $component_factory;

    public function __construct(
        int $a_ref_id = 0,
        int $a_id_type = self::REPOSITORY_NODE_ID,
        int $a_parent_node_id = 0
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->slot_request = $DIC->repository()
                                  ->internal()
                                  ->gui()
                                  ->pluginSlot()
                                  ->request();

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->tabs = $DIC->tabs();
        $this->locator = $DIC["ilLocator"];
        $this->user = $DIC->user();
        $this->component_factory = $DIC["component.factory"];
        $this->component_repository = $DIC["component.repository"];
        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
        $this->plugin = $this->getPlugin();
    }

    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilNavigationHistory = $this->nav_history;
        $ilTabs = $this->tabs;

        // get standard template (includes main menu and general layout)
        $tpl->loadStandardTemplate();

        // set title
        if (!$this->getCreationMode()) {
            $tpl->setTitle($this->object->getTitle());
            $tpl->setTitleIcon(ilObject::_getIcon($this->object->getId()));

            // set tabs
            if (strtolower($this->slot_request->getBaseClass()) !== "iladministrationgui") {
                $this->setTabs();
                $this->setLocator();
            } else {
                $this->addAdminLocatorItems();
                $tpl->setLocator();
                $this->setAdminTabs();
            }

            if ($ilAccess->checkAccess('read', '', $this->object->getRefId())) {
                $ilNavigationHistory->addItem(
                    $this->object->getRefId(),
                    ilLink::_getLink($this->object->getRefId(), $this->object->getType()),
                    $this->object->getType()
                );
            }
        } else {
            // show info of parent
            $tpl->setTitle($this->lookupParentTitleInCreationMode());
            $tpl->setTitleIcon(
                ilObject::_getIcon(ilObject::_lookupObjId(
                    $this->slot_request->getRefId()
                ), "big"),
                $lng->txt("obj_" . ilObject::_lookupType(
                    $this->slot_request->getRefId(),
                    true
                ))
            );
            $this->setLocator();
        }

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case "ilinfoscreengui":
                $this->checkPermission("visible");
                $this->infoScreen();    // forwards command
                break;

            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $ilTabs->setTabActive("perm_settings");
                $ilCtrl->forwardCommand($perm_gui);
                break;

            case 'ilobjectcopygui':
                $cp = new ilObjectCopyGUI($this);
                $cp->setType($this->getType());
                $this->ctrl->forwardCommand($cp);
                break;

            case 'ilexportgui':
                // only if plugin supports it?
                $this->tabs->setTabActive("export");
                $exp = new ilExportGUI($this);
                $exp->addFormat('xml');
                $this->ctrl->forwardCommand($exp);
                break;

            case 'illearningprogressgui':
                $user_id = $this->user->getId();
                if ($this->slot_request->getUserId() > 0 && $this->access->checkAccess(
                    'write',
                    "",
                    $this->object->getRefId()
                )) {
                    $user_id = $this->slot_request->getUserId();
                }
                $ilTabs->setTabActive("learning_progress");
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressBaseGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $user_id
                );
                $this->ctrl->forwardCommand($new_gui);
                break;
            case 'ilcommonactiondispatchergui':
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                if ($cmd === "save" && $this->getCreationMode()) {
                    $this->$cmd();
                    return;
                }
                if (!$cmd) {
                    $cmd = $this->getStandardCmd();
                }
                if ($cmd === "infoScreen") {
                    $ilCtrl->setCmd("showSummary");
                    $ilCtrl->setCmdClass("ilinfoscreengui");
                    $this->infoScreen();
                } else {
                    $this->performCommand($cmd);
                }
                break;
        }

        if (!$this->getCreationMode()) {
            $tpl->printToStdout();
        }
    }

    protected function addLocatorItems() : void
    {
        $ilLocator = $this->locator;

        if (!$this->getCreationMode()) {
            $ilLocator->addItem(
                $this->object->getTitle(),
                $this->ctrl->getLinkTarget($this, $this->getStandardCmd()),
                "",
                $this->slot_request->getRefId()
            );
        }
    }

    /**
     * Get plugin object
     * @throws ilPluginException
     */
    protected function getPlugin() : ilPlugin
    {
        if (!$this->plugin) {
            $this->plugin = $this->component_factory->getPlugin($this->getType());
        }
        return $this->plugin;
    }

    final protected function txt(string $a_var) : string
    {
        return $this->getPlugin()->txt($a_var);
    }

    /**
     * Use custom creation form titles
     */
    protected function getCreationFormTitle(int $a_form_type) : string
    {
        switch ($a_form_type) {
            case self::CFORM_NEW:
                return $this->txt($this->getType() . "_new");

            case self::CFORM_IMPORT:
                return $this->lng->txt("import");

            case self::CFORM_CLONE:
                return $this->txt("objs_" . $this->getType() . "_duplicate");
        }
        return "";
    }

    /**
     * Init creation forms
     * this will create the default creation forms: new, import, clone
     */
    protected function initCreationForms(string $new_type) : array
    {
        $forms = [];
        $forms[self::CFORM_NEW] = $this->initCreateForm($new_type);

        if ($this->supportsExport()) {
            $forms[self::CFORM_IMPORT] = $this->initImportForm($new_type);
        }
        if ($this->supportsCloning()) {
            $forms[self::CFORM_CLONE] = $this->fillCloneTemplate(null, $new_type);
        }

        return $forms;
    }

    /**
     * @return bool returns true if this plugin object supports cloning
     */
    protected function supportsCloning() : bool
    {
        return true;
    }

    /**
     * Init object creation form
     */
    protected function initCreateForm(string $new_type) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($this->ctrl->getFormAction($this, "save"));
        $form->setTitle($this->txt($new_type . "_new"));

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $form->addItem($ta);

        $form->addCommandButton("save", $this->txt($new_type . "_add"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * Init object update form
     */
    protected function initEditForm() : ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($ilCtrl->getFormAction($this, "update"));
        $form->setTitle($lng->txt("edit"));

        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setSize(min(40, ilObject::TITLE_LENGTH));
        $ti->setMaxLength(ilObject::TITLE_LENGTH);
        $ti->setRequired(true);
        $form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $form->addItem($ta);

        $form->addCommandButton("update", $lng->txt("save"));
        // $this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));

        return $form;
    }

    /**
     * Init object import form
     * @param string    new type
     * @return    ilPropertyFormGUI
     */
    protected function initImportForm(string $new_type) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($this->ctrl->getFormAction($this, "importFile"));
        $form->setTitle($this->lng->txt("import"));

        $fi = new ilFileInputGUI($this->lng->txt("import_file"), "importfile");
        $fi->setSuffixes(["zip"]);
        $fi->setRequired(true);
        $form->addItem($fi);

        $form->addCommandButton("importFile", $this->lng->txt("import"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }

    protected function afterSave(ilObject $new_object) : void
    {
        $ilCtrl = $this->ctrl;
        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);

        $ilCtrl->setTargetScript('ilias.php');
        $ilCtrl->setParameterByClass(get_class($this), "ref_id", $new_object->getRefId());
        $ilCtrl->redirectByClass(["ilobjplugindispatchgui", get_class($this)], $this->getAfterCreationCmd());
    }

    /**
     * Cmd that will be redirected to after creation of a new object.
     */
    abstract public function getAfterCreationCmd() : string;

    abstract public function getStandardCmd() : string;

    abstract public function performCommand(string $cmd) : void;

    public function addInilPluginAdminfoTab() : void
    {
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;

        // info screen
        if ($ilAccess->checkAccess('visible', "", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "info_short",
                $this->ctrl->getLinkTargetByClass(
                    "ilinfoscreengui",
                    "showSummary"
                ),
                "showSummary"
            );
        }
    }

    public function addPermissionTab() : void
    {
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        // edit permissions
        if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "perm_settings",
                $ilCtrl->getLinkTargetByClass("ilpermissiongui", "perm"),
                ["perm", "info", "owner"],
                'ilpermissiongui'
            );
        }
    }

    public function addExportTab() : void
    {
        // write
        if ($this->access->checkAccess('write', "", $this->object->getRefId())) {
            $this->tabs->addTarget(
                'export',
                $this->ctrl->getLinkTargetByClass("ilexportgui", ''),
                'export',
                'ilexportgui'
            );
        }
    }

    public function infoScreen() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $ilTabs->activateTab("info_short");

        $this->checkPermission("visible");

        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();

        // general information
        $lng->loadLanguageModule("meta");

        $this->addInfoItems($info);

        // forward the command
        $ilCtrl->forwardCommand($info);
    }

    /**
     * Add items to info screen
     */
    public function addInfoItems(ilInfoScreenGUI $info) : void
    {
    }

    /**
     * Goto redirection
     */
    public static function _goto(string $a_target) : void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilCtrl = $DIC->ctrl();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();

        $t = explode("_", $a_target[0]);
        $ref_id = (int) $t[0];
        $class_name = $a_target[1];

        if ($ilAccess->checkAccess("read", "", $ref_id)) {
            $ilCtrl->setTargetScript('ilias.php');
            $ilCtrl->setParameterByClass($class_name, "ref_id", $ref_id);
            $ilCtrl->redirectByClass(["ilobjplugindispatchgui", $class_name], "");
        } elseif ($ilAccess->checkAccess("visible", "", $ref_id)) {
            $ilCtrl->setTargetScript('ilias.php');
            $ilCtrl->setParameterByClass($class_name, "ref_id", $ref_id);
            $ilCtrl->redirectByClass(["ilobjplugindispatchgui", $class_name], "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id))
            ));
            ilObjectGUI::_gotoRepositoryRoot();
        }
    }

    protected function supportsExport() : bool
    {
        $component_repository = $this->component_repository;

        return $component_repository->getPluginSlotById("robj")->getPluginByName($this->getPlugin()->getPluginName())->supportsExport();
    }

    protected function lookupParentTitleInCreationMode() : string
    {
        return ilObject::_lookupTitle(ilObject::_lookupObjId(
            $this->slot_request->getRefId()
        ));
    }
}
