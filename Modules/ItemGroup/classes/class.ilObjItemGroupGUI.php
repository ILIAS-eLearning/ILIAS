<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject2GUI.php");
include_once("./Modules/ItemGroup/classes/class.ilObjItemGroup.php");

/**
 * User Interface class for item groups
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * $Id$
 *
 * @ilCtrl_Calls ilObjItemGroupGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjItemGroupGUI: ilCommonActionDispatcherGUI, ilObjectCopyGUI
 * @ilCtrl_isCalledBy ilObjItemGroupGUI: ilRepositoryGUI, ilAdministrationGUI
 * @ingroup ModulesItemGroup
 */
class ilObjItemGroupGUI extends ilObject2GUI
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilErrorHandling
     */
    protected $error;


    /**
     * Constructor
     */
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC;
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->locator = $DIC["ilLocator"];
        $this->tree = $DIC->repositoryTree();
        $this->help = $DIC["ilHelp"];
        $this->error = $DIC["ilErr"];
    }

    /**
     * Initialisation
     */
    protected function afterConstructor()
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("itgr");
        
        $this->ctrl->saveParameter($this, array("ref_id"));
    }

    /**
     * Get type
     */
    final public function getType()
    {
        return "itgr";
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilAccess = $this->access;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilLocator = $this->locator;
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case 'ilinfoscreengui':
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->infoScreen();
                break;

            case 'ilpermissiongui':
                $this->prepareOutput();
                $ilTabs->activateTab("perm_settings");
                $this->addHeaderAction();
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $cmd = $this->ctrl->getCmd("listMaterials");
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->$cmd();
                break;
        }
    }

    /**
     * Add session locator
     *
     * @access public
     *
     */
    public function addLocatorItems()
    {
        $ilLocator = $this->locator;
        $ilAccess = $this->access;
        
        if (is_object($this->object) && $ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "listMaterials"), "", $_GET["ref_id"]);
        }
    }

    protected function initCreationForms($a_new_type)
    {
        $forms = array(self::CFORM_NEW => $this->initCreateForm($a_new_type)
            );

        return $forms;
    }

    /**
     * Init edit form, custom part
     *
     * @param ilPropertyFormGUI $a_form form object
     */
    public function initEditCustomForm(ilPropertyFormGUI $a_form)
    {
        $a_form->removeItemByPostVar("desc");

        // description
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setRows(2);
        $ta->setInfo($this->lng->txt("itgr_desc_info"));
        $a_form->addItem($ta);

        // show title
        $cb = new ilCheckboxInputGUI($this->lng->txt("itgr_show_title"), "show_title");
        $cb->setInfo($this->lng->txt("itgr_show_title_info"));
        $a_form->addItem($cb);

        // behaviour
        include_once("./Modules/ItemGroup/classes/class.ilItemGroupBehaviour.php");
        $options = ilItemGroupBehaviour::getAll();
        $si = new ilSelectInputGUI($this->lng->txt("itgr_behaviour"), "behaviour");
        $si->setInfo($this->lng->txt("itgr_behaviour_info"));
        $si->setOptions($options);
        $cb->addSubItem($si);
    }


    /**
     * After save
     */
    protected function afterSave(ilObject $a_new_object)
    {
        $ilCtrl = $this->ctrl;
        
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);
        $ilCtrl->redirect($this, "listMaterials");
    }

    /**
     * show material assignment
     *
     * @access protected
     * @param
     * @return
     */
    public function listMaterials()
    {
        $tree = $this->tree;
        $ilTabs = $this->tabs;
        $tpl = $this->tpl;
        
        $this->checkPermission("write");
        
        $ilTabs->activateTab("materials");
                
        $parent_ref_id = $tree->getParentId($this->object->getRefId());
        
        include_once "Services/Object/classes/class.ilObjectAddNewItemGUI.php";
        $gui = new ilObjectAddNewItemGUI($parent_ref_id);
        $gui->setDisabledObjectTypes(array("itgr", "sess"));
        $gui->setAfterCreationCallback($this->object->getRefId());
        $gui->render();
        
        include_once("./Modules/ItemGroup/classes/class.ilItemGroupItemsTableGUI.php");
        $tab = new ilItemGroupItemsTableGUI($this, "listMaterials");
        $tpl->setContent($tab->getHTML());
    }
    
    /**
     * Save material assignment
     */
    public function saveItemAssignment()
    {
        $ilCtrl = $this->ctrl;
        
        $this->checkPermission("write");

        include_once './Modules/ItemGroup/classes/class.ilItemGroupItems.php';

        $item_group_items = new ilItemGroupItems($this->object->getRefId());
        $items = is_array($_POST['items'])
            ? $_POST['items']
            : array();
        $items = ilUtil::stripSlashesArray($items);
        $item_group_items->setItems($items);
        $item_group_items->update();

        ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
        $ilCtrl->redirect($this, "listMaterials");
    }

    
    /**
    * Get standard template
    */
    public function getTemplate()
    {
        $this->tpl->getStandardTemplate();
    }


    /**
     * Set tabs
     */
    public function setTabs()
    {
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $ilHelp = $this->help;
        $lng = $this->lng;
        $tree = $this->tree;
        
        $ilHelp->setScreenIdComponent("itgr");
        
        $parent_ref_id = $tree->getParentId($this->object->getRefId());
        $parent_obj_id = ilObject::_lookupObjId($parent_ref_id);
        $parent_type = ilObject::_lookupType($parent_obj_id);
        
        include_once("./Services/Link/classes/class.ilLink.php");
        $ilTabs->setBackTarget(
            $lng->txt('obj_' . $parent_type),
            ilLink::_getLink($parent_ref_id),
            "_top"
        );
        
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilTabs->addTab(
                'materials',
                $lng->txt('itgr_materials'),
                $this->ctrl->getLinkTarget($this, 'listMaterials')
            );

            $ilTabs->addTab(
                'settings',
                $lng->txt('settings'),
                $this->ctrl->getLinkTarget($this, 'edit')
            );
        }
        
        if ($ilAccess->checkAccess("edit_permission", "", $this->object->getRefId())) {
            $ilTabs->addTab(
                "perm_settings",
                $lng->txt('perm_settings'),
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm")
            );
        }
    }


    /**
     * Goto item group
     */
    public static function _goto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();
        $tree = $DIC->repositoryTree();
        
        $targets = explode('_', $a_target);
        $ref_id = $targets[0];
        $par_id = $tree->getParentId($ref_id);
        
        if ($ilAccess->checkAccess("read", "", $par_id)) {
            include_once("./Services/Link/classes/class.ilLink.php");
            ilUtil::redirect(ilLink::_getLink($par_id));
            exit;
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            ilUtil::sendFailure(sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }

    /**
     * Goto item group
     */
    public function gotoParent()
    {
        $ilAccess = $this->access;
        $ilErr = $this->error;
        $lng = $this->lng;
        $tree = $this->tree;
        
        $ref_id = $this->object->getRefId();
        $par_id = $tree->getParentId($ref_id);
        
        if ($ilAccess->checkAccess("read", "", $par_id)) {
            include_once("./Services/Link/classes/class.ilLink.php");
            ilUtil::redirect(ilLink::_getLink($par_id));
            exit;
        }
    }

    /**
     * Custom callback after object is created (in parent containert
     *
     * @param ilObject $a_obj
     */
    public function afterSaveCallback(ilObject $a_obj)
    {
        // add new object to materials
        include_once './Modules/ItemGroup/classes/class.ilItemGroupItems.php';
        $items = new ilItemGroupItems($this->object->getRefId());
        $items->addItem($a_obj->getRefId());
        $items->update();
    }

    /**
     * Get edit form values (custom part)
     *
     * @param array $a_values form values
     */
    public function getEditFormCustomValues(array &$a_values)
    {
        $a_values["show_title"] = !$this->object->getHideTitle();
        $a_values["behaviour"] = $this->object->getBehaviour();
    }

    /**
     * Update (custom part)
     *
     * @param ilPropertyFormGUI $a_form form
     */
    public function updateCustom(ilPropertyFormGUI $a_form)
    {
        $this->object->setHideTitle(!$a_form->getInput("show_title"));
        include_once("./Modules/ItemGroup/classes/class.ilItemGroupBehaviour.php");
        $behaviour = ($a_form->getInput("show_title"))
            ? $a_form->getInput("behaviour")
            : ilItemGroupBehaviour::ALWAYS_OPEN;
        $this->object->setBehaviour($behaviour);
    }

    /**
     * Init object creation form
     *
     * @param	string	$a_new_type
     * @return	ilPropertyFormGUI
     */
    protected function initCreateForm($a_new_type)
    {
        $form = parent::initCreateForm($a_new_type);
        $ta = $form->getItemByPostVar("desc");
        $ta->setInfo($this->lng->txt("itgr_desc_info"));
        return $form;
    }
}
