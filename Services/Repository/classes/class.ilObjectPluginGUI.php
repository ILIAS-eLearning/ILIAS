<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject2GUI.php");
include_once("./Services/Component/classes/class.ilPlugin.php");

/*
* Object GUI class for plugins
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesRepository
*/
abstract class ilObjectPluginGUI extends ilObject2GUI
{
    /**
     * @var ilNavigationHistory
     */
    protected $nav_history;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;


    protected $plugin;
    /**
    * Constructor.
    */
    public function __construct($a_ref_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->tabs = $DIC->tabs();
        $this->locator = $DIC["ilLocator"];
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->user = $DIC->user();
        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
        $this->plugin = $this->getPlugin();
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilNavigationHistory = $this->nav_history;
        $ilTabs = $this->tabs;

        // get standard template (includes main menu and general layout)
        $tpl->getStandardTemplate();

        // set title
        if (!$this->getCreationMode()) {
            $tpl->setTitle($this->object->getTitle());
            $tpl->setTitleIcon(ilObject::_getIcon($this->object->getId()));

            // set tabs
            if (strtolower($_GET["baseClass"]) != "iladministrationgui") {
                $this->setTabs();
                $this->setLocator();
            } else {
                $this->addAdminLocatorItems();
                $tpl->setLocator();
                $this->setAdminTabs();
            }
            
            // add entry to navigation history
            if ($ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
                $ilNavigationHistory->addItem(
                    $_GET["ref_id"],
                    $ilCtrl->getLinkTarget($this, $this->getStandardCmd()),
                    $this->getType()
                );
            }
        } else {
            // show info of parent
            $tpl->setTitle($this->lookupParentTitleInCreationMode());
            $tpl->setTitleIcon(
                ilObject::_getIcon(ilObject::_lookupObjId($_GET["ref_id"]), "big"),
                $lng->txt("obj_" . ilObject::_lookupType($_GET["ref_id"], true))
            );
            $this->setLocator();
        }

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case "ilinfoscreengui":
                $this->checkPermission("visible");
                $this->infoScreen();	// forwards command
                break;

            case 'ilpermissiongui':
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ilTabs->setTabActive("perm_settings");
                $ilCtrl->forwardCommand($perm_gui);
                break;
        
            case 'ilobjectcopygui':
                include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
                $cp = new ilObjectCopyGUI($this);
                $cp->setType($this->getType());
                $this->ctrl->forwardCommand($cp);
                break;
            
            case 'ilexportgui':
                // only if plugin supports it?
                $this->tabs->setTabActive("export");
                include_once './Services/Export/classes/class.ilExportGUI.php';
                $exp = new ilExportGUI($this);
                $exp->addFormat('xml');
                $this->ctrl->forwardCommand($exp);
                break;
                        
            case 'illearningprogressgui':
                $ilTabs->setTabActive("learning_progress");
                include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $_GET['user_id'] ? $_GET['user_id'] : $GLOBALS['ilUser']->getId()
                );
                $this->ctrl->forwardCommand($new_gui);
                break;
            case 'ilcommonactiondispatchergui':
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                if ($this->getCreationMode() || $cmd == "save") {
                    $this->$cmd();
                    return;
                }
//				if (strtolower($_GET["baseClass"]) == "iladministrationgui")
//				{
//					$this->viewObject();
//					return;
//				}
                if (!$cmd) {
                    $cmd = $this->getStandardCmd();
                }
                if ($cmd == "infoScreen") {
                    $ilCtrl->setCmd("showSummary");
                    $ilCtrl->setCmdClass("ilinfoscreengui");
                    $this->infoScreen();
                } else {
                    $this->performCommand($cmd);
                }
                break;
        }

        if (!$this->getCreationMode()) {
            $tpl->show();
        }
    }

    /**
    * Add object to locator
    */
    public function addLocatorItems()
    {
        $ilLocator = $this->locator;

        if (!$this->getCreationMode()) {
            $ilLocator->addItem(
                $this->object->getTitle(),
                $this->ctrl->getLinkTarget($this, $this->getStandardCmd()),
                "",
                $_GET["ref_id"]
            );
        }
    }

    /**
     * Get plugin object
     * @return object plugin object
     * @throws ilPluginException
     */
    protected function getPlugin()
    {
        if (!$this->plugin) {
            $this->plugin =
                ilPlugin::getPluginObject(
                    IL_COMP_SERVICE,
                    "Repository",
                    "robj",
                    ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $this->getType())
                );
            if (!is_object($this->plugin)) {
                throw new ilPluginException("ilObjectPluginGUI: Could not instantiate plugin object for type " . $this->getType() . ".");
            }
        }
        return $this->plugin;
    }
    
    /**
    * Wrapper for txt function
    */
    final protected function txt($a_var)
    {
        return $this->getPlugin()->txt($a_var);
    }
    
    /**
     * Use custom creation form titles
     *
     * @param string $a_form_type
     * @return string
     */
    protected function getCreationFormTitle($a_form_type)
    {
        switch ($a_form_type) {
            case self::CFORM_NEW:
                return $this->txt($this->getType() . "_new");
                
            case self::CFORM_IMPORT:
                return $this->lng->txt("import");
                
            case self::CFORM_CLONE:
                return $this->txt("objs_" . $this->getType() . "_duplicate");
        }
    }
    
    /**
     * Init creation froms
     *
     * this will create the default creation forms: new, import, clone
     *
     * @param	string	$a_new_type
     * @return	array
     */
    protected function initCreationForms($a_new_type)
    {
        $ilPluginAdmin = $this->plugin_admin;
        
        $forms = array();
        $forms[self::CFORM_NEW] = $this->initCreateForm($a_new_type);

        if ($this->supportsExport()) {
            $forms[self::CFORM_IMPORT] = $this->initImportForm($a_new_type);
        }
        if ($this->supportsCloning()) {
            $forms[self::CFORM_CLONE] = $this->fillCloneTemplate(null, $a_new_type);
        }

        return $forms;
    }

    /**
     * @return bool returns true iff this plugin object supports cloning
     */
    protected function supportsCloning()
    {
        return true;
    }
    
    /**
    * Init object creation form
    *
    * @param	string	$a_new_type
    * @return	ilPropertyFormGUI
    */
    public function initCreateForm($a_new_type)
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($this->ctrl->getFormAction($this, "save"));
        $form->setTitle($this->txt($a_new_type . "_new"));

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

        $form->addCommandButton("save", $this->txt($a_new_type . "_add"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }
    
    /**
    * Init object update form
    *
    * @return	ilPropertyFormGUI
    */
    public function initEditForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
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
     *
     * @param	string	new type
     * @return	ilPropertyFormGUI
     */
    protected function initImportForm($a_new_type)
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($this->ctrl->getFormAction($this, "importFile"));
        $form->setTitle($this->lng->txt("import"));

        include_once("./Services/Form/classes/class.ilFileInputGUI.php");
        $fi = new ilFileInputGUI($this->lng->txt("import_file"), "importfile");
        $fi->setSuffixes(array("zip"));
        $fi->setRequired(true);
        $form->addItem($fi);

        $form->addCommandButton("importFile", $this->lng->txt("import"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));
    
        return $form;
    }

    /**
    * After saving
    * @access	public
    */
    public function afterSave(ilObject $newObj)
    {
        $ilCtrl = $this->ctrl;
        // always send a message
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);

        $ilCtrl->initBaseClass("ilObjPluginDispatchGUI");
        $ilCtrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
        
        $ilCtrl->setParameterByClass(get_class($this), "ref_id", $newObj->getRefId());
        $ilCtrl->redirectByClass(array("ilobjplugindispatchgui", get_class($this)), $this->getAfterCreationCmd());
    }
    
    /**
    * Cmd that will be redirected to after creation of a new object.
    */
    abstract public function getAfterCreationCmd();
    
    abstract public function getStandardCmd();
    
    //	abstract function performCommand();
    
    /**
    * Add info screen tab
    */
    public function addInfoTab()
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

    /**
    * Add permission tab
    */
    public function addPermissionTab()
    {
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        
        // edit permissions
        if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "perm_settings",
                $ilCtrl->getLinkTargetByClass("ilpermissiongui", "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }
    
    
    /**
    * Add export tab
    */
    public function addExportTab()
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

    /**
    * show information screen
    */
    public function infoScreen()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("info_short");
        
        $this->checkPermission("visible");

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
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
     * @param ilInfoScreenGUI $info
     */
    public function addInfoItems($info)
    {
    }

    /**
    * Goto redirection
    */
    public static function _goto($a_target)
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        
        $t = explode("_", $a_target[0]);
        $ref_id = (int) $t[0];
        $class_name = $a_target[1];
        
        if ($ilAccess->checkAccess("read", "", $ref_id)) {
            $ilCtrl->initBaseClass("ilObjPluginDispatchGUI");
            $ilCtrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
            $ilCtrl->setParameterByClass($class_name, "ref_id", $ref_id);
            $ilCtrl->redirectByClass(array("ilobjplugindispatchgui", $class_name), "");
        } elseif ($ilAccess->checkAccess("visible", "", $ref_id)) {
            $ilCtrl->initBaseClass("ilObjPluginDispatchGUI");
            $ilCtrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
            $ilCtrl->setParameterByClass($class_name, "ref_id", $ref_id);
            $ilCtrl->redirectByClass(array("ilobjplugindispatchgui", $class_name), "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            ilUtil::sendFailure(sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id))
            ));
            include_once("./Services/Object/classes/class.ilObjectGUI.php");
            ilObjectGUI::_gotoRepositoryRoot();
        }
    }


    /**
     * @return bool
     */
    protected function supportsExport()
    {
        $ilPluginAdmin = $this->plugin_admin;

        return $ilPluginAdmin->supportsExport(IL_COMP_SERVICE, "Repository", "robj", $this->getPlugin()->getPluginName());
    }


    /**
     * @return mixed
     */
    protected function lookupParentTitleInCreationMode()
    {
        return ilObject::_lookupTitle(ilObject::_lookupObjId($_GET["ref_id"]));
    }
}
