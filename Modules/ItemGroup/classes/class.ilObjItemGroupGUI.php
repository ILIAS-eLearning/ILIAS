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

use ILIAS\ItemGroup\StandardGUIRequest;

/**
 * User Interface class for item groups
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjItemGroupGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjItemGroupGUI: ilCommonActionDispatcherGUI, ilObjectCopyGUI, ilObjectTranslationGUI
 * @ilCtrl_isCalledBy ilObjItemGroupGUI: ilRepositoryGUI, ilAdministrationGUI
 */
class ilObjItemGroupGUI extends ilObject2GUI
{
    protected StandardGUIRequest $ig_request;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;

    public function __construct(
        int $a_id = 0,
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

    protected function afterConstructor(): void
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("itgr");
        $this->ctrl->saveParameter($this, array("ref_id"));
    }

    final public function getType(): string
    {
        return "itgr";
    }

    public function executeCommand(): void
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

            case 'ilobjecttranslationgui':
                $this->checkPermissionBool("write");
                $this->prepareOutput();
                $this->setSettingsSubTabs("settings_trans");
                $transgui = new ilObjectTranslationGUI($this);
                $transgui->setTitleDescrOnlyMode(false);
                $this->ctrl->forwardCommand($transgui);
                break;

            default:
                $cmd = $this->ctrl->getCmd("listMaterials");
                $this->prepareOutput();
                $this->addHeaderAction();
                $this->$cmd();
                break;
        }
    }

    public function addLocatorItems(): void
    {
        $ilLocator = $this->locator;
        $ilAccess = $this->access;

        if (is_object($this->object) && $ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "listMaterials"), "", $this->requested_ref_id);
        }
    }

    protected function initCreationForms(string $new_type): array
    {
        $forms = array(self::CFORM_NEW => $this->initCreateForm($new_type));

        return $forms;
    }

    protected function initEditCustomForm(ilPropertyFormGUI $form): void
    {
        $form->removeItemByPostVar("desc");

        // presentation
        $pres = new ilFormSectionHeaderGUI();
        $pres->setTitle($this->lng->txt('obj_presentation'));
        $form->addItem($pres);

        // show title
        $cb = new ilCheckboxInputGUI($this->lng->txt("itgr_show_title"), "show_title");
        $cb->setInfo($this->lng->txt("itgr_show_title_info"));
        $form->addItem($cb);

        // behaviour
        $options = ilItemGroupBehaviour::getAll();
        $si = new ilSelectInputGUI($this->lng->txt("itgr_behaviour"), "behaviour");
        $si->setInfo($this->lng->txt("itgr_behaviour_info"));
        $si->setOptions($options);
        $cb->addSubItem($si);

        // tile/list
        $lpres = new ilRadioGroupInputGUI($this->lng->txt('itgr_list_presentation'), "list_presentation");

        $std_list = new ilRadioOption($this->lng->txt('itgr_list_default'), "");
        $std_list->setInfo($this->lng->txt('itgr_list_default_info'));
        $lpres->addOption($std_list);

        $item_list = new ilRadioOption($this->lng->txt('itgr_list'), "list");
        $lpres->addOption($item_list);

        $tile_view = new ilRadioOption($this->lng->txt('itgr_tile'), "tile");
        $lpres->addOption($tile_view);

        // tile size
        $si = new ilRadioGroupInputGUI($this->lng->txt("itgr_tile_size"), "tile_size");
        $dummy_container = new ilContainer();
        $this->lng->loadLanguageModule("cont");
        foreach ($dummy_container->getTileSizes() as $key => $txt) {
            $op = new ilRadioOption($txt, $key);
            $si->addOption($op);
        }
        $lpres->addSubItem($si);
        $si->setValue($this->object->getTileSize());

        $lpres->setValue($this->object->getListPresentation());
        $form->addItem($lpres);
    }

    protected function afterSave(ilObject $new_object): void
    {
        $ilCtrl = $this->ctrl;

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $ilCtrl->redirect($this, "listMaterials");
    }

    public function edit(): void
    {
        parent::edit();
        $this->setSettingsSubTabs("general");
    }

    public function listMaterials(): void
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

    public function saveItemAssignment(): void
    {
        $ilCtrl = $this->ctrl;

        $this->checkPermission("write");

        $item_group_items = new ilItemGroupItems($this->object->getRefId());
        $items = $this->ig_request->getItems();
        $item_group_items->setItems($items);
        $item_group_items->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $ilCtrl->redirect($this, "listMaterials");
    }

    public function getTemplate(): void
    {
        $this->tpl->loadStandardTemplate();
    }

    protected function setTabs(): void
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

    protected function setSettingsSubTabs(string $active_tab = "general"): void
    {
        $this->tabs_gui->addSubTab(
            "general",
            $this->lng->txt("settings"),
            $this->ctrl->getLinkTarget($this, "edit")
        );

        $this->tabs_gui->addSubTab(
            "settings_trans",
            $this->lng->txt("obj_multilinguality"),
            $this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", "")
        );
        $this->tabs_gui->activateTab("settings");
        $this->tabs_gui->activateSubTab($active_tab);
    }

    public static function _goto(string $a_target): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

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
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        throw new ilPermissionException($lng->txt("msg_no_perm_read"));
    }

    public function gotoParent(): void
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
    public function afterSaveCallback(ilObject $a_obj): void
    {
        // add new object to materials
        $items = new ilItemGroupItems($this->object->getRefId());
        $items->addItem($a_obj->getRefId());
        $items->update();
    }

    /**
     * Get edit form values (custom part)
     */
    protected function getEditFormCustomValues(array &$a_values): void
    {
        $a_values["show_title"] = !$this->object->getHideTitle();
        $a_values["behaviour"] = $this->object->getBehaviour();
        $a_values["list_presentation"] = $this->object->getListPresentation();
        $a_values["tile_size"] = $this->object->getTileSize();
    }

    protected function updateCustom(ilPropertyFormGUI $form): void
    {
        $this->object->setHideTitle(!$form->getInput("show_title"));
        $behaviour = ($form->getInput("show_title"))
            ? $form->getInput("behaviour")
            : ilItemGroupBehaviour::ALWAYS_OPEN;
        $this->object->setBehaviour($behaviour);
        $this->object->setListPresentation($form->getInput("list_presentation"));
        $this->object->setTileSize($form->getInput("tile_size"));
    }

    protected function initCreateForm(string $new_type): ilPropertyFormGUI
    {
        $form = parent::initCreateForm($new_type);
        $form->removeItemByPostVar("desc");
        return $form;
    }
}
