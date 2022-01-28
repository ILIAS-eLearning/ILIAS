<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\ItemGroup\StandardGUIRequest;

/**
 * User Interface class for item groups
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjItemGroupGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjItemGroupGUI: ilCommonActionDispatcherGUI, ilObjectCopyGUI
 * @ilCtrl_isCalledBy ilObjItemGroupGUI: ilRepositoryGUI, ilAdministrationGUI
 */
class ilObjItemGroupGUI extends ilObject2GUI
{
    protected StandardGUIRequest $ig_request;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;

    public function __construct(
        ?int $a_id = 0,
        int $a_id_type = self::REPOSITORY_NODE_ID,
        int $a_parent_node_id = 0
    ) {
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
        $this->ig_request = $DIC->itemGroup()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    protected function afterConstructor()
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("itgr");
        $this->ctrl->saveParameter($this, array("ref_id"));
    }

    final public function getType()
    {
        return "itgr";
    }
    
    public function executeCommand() : void
    {
        $ilTabs = $this->tabs;
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->prepareOutput();
                $ilTabs->activateTab("perm_settings");
                $this->addHeaderAction();
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilcommonactiondispatchergui":
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

    public function addLocatorItems() : void
    {
        $ilLocator = $this->locator;
        $ilAccess = $this->access;
        
        if (is_object($this->object) && $ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "listMaterials"), "", $this->requested_ref_id);
        }
    }

    protected function initCreationForms($a_new_type)
    {
        $forms = array(self::CFORM_NEW => $this->initCreateForm($a_new_type)
            );

        return $forms;
    }

    protected function initEditCustomForm(ilPropertyFormGUI $a_form)
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
        $options = ilItemGroupBehaviour::getAll();
        $si = new ilSelectInputGUI($this->lng->txt("itgr_behaviour"), "behaviour");
        $si->setInfo($this->lng->txt("itgr_behaviour_info"));
        $si->setOptions($options);
        $cb->addSubItem($si);
    }

    protected function afterSave(ilObject $a_new_object)
    {
        $ilCtrl = $this->ctrl;
        
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);
        $ilCtrl->redirect($this, "listMaterials");
    }

    public function listMaterials() : void
    {
        $tree = $this->tree;
        $ilTabs = $this->tabs;
        $tpl = $this->tpl;
        
        $this->checkPermission("write");
        
        $ilTabs->activateTab("materials");
                
        $parent_ref_id = $tree->getParentId($this->object->getRefId());
        
        $gui = new ilObjectAddNewItemGUI($parent_ref_id);
        $gui->setDisabledObjectTypes(array("itgr", "sess"));
        $gui->setAfterCreationCallback($this->object->getRefId());
        $gui->render();
        
        $tab = new ilItemGroupItemsTableGUI($this, "listMaterials");
        $tpl->setContent($tab->getHTML());
    }
    
    public function saveItemAssignment() : void
    {
        $ilCtrl = $this->ctrl;
        
        $this->checkPermission("write");

        $item_group_items = new ilItemGroupItems($this->object->getRefId());
        $items = $this->ig_request->getItems();
        $item_group_items->setItems($items);
        $item_group_items->update();

        ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
        $ilCtrl->redirect($this, "listMaterials");
    }

    public function getTemplate() : void
    {
        $this->tpl->loadStandardTemplate();
    }

    protected function setTabs() : void
    {
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;
        $ilHelp = $this->help;
        $lng = $this->lng;
        $tree = $this->tree;
        
        $ilHelp->setScreenIdComponent("itgr");
        
        $parent_ref_id = $tree->getParentId($this->object->getRefId());
        $parent_obj_id = ilObject::_lookupObjId($parent_ref_id);
        $parent_type = ilObject::_lookupType($parent_obj_id);
        
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

    public static function _goto(string $a_target) : void
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $tree = $DIC->repositoryTree();
        
        $targets = explode('_', $a_target);
        $ref_id = $targets[0];
        $par_id = $tree->getParentId($ref_id);
        
        if ($ilAccess->checkAccess("read", "", $par_id)) {
            ilUtil::redirect(ilLink::_getLink($par_id));
            exit;
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            ilUtil::sendFailure(sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        throw new ilPermissionException($lng->txt("msg_no_perm_read"));
    }

    public function gotoParent() : void
    {
        $ilAccess = $this->access;
        $tree = $this->tree;
        
        $ref_id = $this->object->getRefId();
        $par_id = $tree->getParentId($ref_id);
        
        if ($ilAccess->checkAccess("read", "", $par_id)) {
            ilUtil::redirect(ilLink::_getLink($par_id));
            exit;
        }
    }

    /**
     * Custom callback after object is created (in parent container)
     */
    public function afterSaveCallback(ilObject $a_obj) : void
    {
        // add new object to materials
        $items = new ilItemGroupItems($this->object->getRefId());
        $items->addItem($a_obj->getRefId());
        $items->update();
    }

    /**
     * Get edit form values (custom part)
     */
    protected function getEditFormCustomValues(array &$a_values) : void
    {
        $a_values["show_title"] = !$this->object->getHideTitle();
        $a_values["behaviour"] = $this->object->getBehaviour();
    }

    protected function updateCustom(ilPropertyFormGUI $a_form)
    {
        $this->object->setHideTitle(!$a_form->getInput("show_title"));
        $behaviour = ($a_form->getInput("show_title"))
            ? $a_form->getInput("behaviour")
            : ilItemGroupBehaviour::ALWAYS_OPEN;
        $this->object->setBehaviour($behaviour);
    }

    protected function initCreateForm($a_new_type)
    {
        $form = parent::initCreateForm($a_new_type);
        $ta = $form->getItemByPostVar("desc");
        $ta->setInfo($this->lng->txt("itgr_desc_info"));
        return $form;
    }
}
