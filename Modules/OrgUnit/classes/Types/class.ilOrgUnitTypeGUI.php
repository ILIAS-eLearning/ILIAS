<?php
/**
 * Class ilOrgUnitTypeGUI
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitTypeGUI
{

    /**
     * @var ilCtrl
     */
    public $ctrl;
    /**
     * @var ilTemplate
     */
    public $tpl;
    /**
     * @var ilTabsGUI
     */
    public $tabs;
    /**
     * @var ilAccessHandler
     */
    protected $access;
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;
    /**
     * @var ilLocatorGUI
     */
    protected $locator;
    /**
     * @var ilLog
     */
    protected $log;
    /**
     * @var ILIAS
     */
    protected $ilias;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var
     */
    protected $parent_gui;


    /**
     * @param ilObjOrgUnitGUI $parent_gui
     */
    public function __construct(ilObjOrgUnitGUI $parent_gui)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilAccess = $DIC['ilAccess'];
        $ilToolbar = $DIC['ilToolbar'];
        $ilLocator = $DIC['ilLocator'];
        $tree = $DIC['tree'];
        $lng = $DIC['lng'];
        $ilLog = $DIC['ilLog'];
        $ilias = $DIC['ilias'];
        $ilTabs = $DIC['ilTabs'];
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->access = $ilAccess;
        $this->locator = $ilLocator;
        $this->toolbar = $ilToolbar;
        $this->tabs = $ilTabs;
        $this->log = $ilLog;
        $this->lng = $lng;
        $this->ilias = $ilias;
        $this->parent_gui = $parent_gui;
        $this->lng->loadLanguageModule('orgu');
        $this->ctrl->saveParameter($this, 'type_id');
        $this->lng->loadLanguageModule('meta');
        $this->checkAccess();
    }


    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            case '':
                switch ($cmd) {
                    case '':
                    case 'listTypes':
                        $this->listTypes();
                        break;
                    case 'add':
                        $this->add();
                        break;
                    case 'edit':
                        $this->setSubTabsEdit('general');
                        $this->edit();
                        break;
                    case 'editCustomIcons':
                        $this->setSubTabsEdit('custom_icons');
                        $this->editCustomIcons();
                        break;
                    case 'editAMD':
                        $this->setSubTabsEdit('amd');
                        $this->editAMD();
                        break;
                    case 'updateAMD':
                        $this->setSubTabsEdit('amd');
                        $this->updateAMD();
                        break;
                    case 'updateCustomIcons':
                        $this->setSubTabsEdit('custom_icons');
                        $this->updateCustomIcons();
                        break;
                    case 'create':
                        $this->create();
                        break;
                    case 'update':
                        $this->setSubTabsEdit('general');
                        $this->update();
                        break;
                    case 'delete':
                        $this->delete();
                        break;
                }
                break;
        }
    }


    /**
     * Check if user can edit types
     */
    protected function checkAccess()
    {
        if (!$this->access->checkAccess("write", "", $this->parent_gui->object->getRefId())) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this->parent_gui);
        }
    }


    /**
     * Add subtabs for editing type
     */
    protected function setSubTabsEdit($active_tab_id)
    {
        $this->tabs->addSubTab('general', $this->lng->txt('meta_general'), $this->ctrl->getLinkTarget($this, 'edit'));
        if ($this->ilias->getSetting('custom_icons')) {
            $this->tabs->addSubTab('custom_icons', $this->lng->txt('icon_settings'), $this->ctrl->getLinkTarget($this, 'editCustomIcons'));
        }
        if (count(ilOrgUnitType::getAvailableAdvancedMDRecordIds())) {
            $this->tabs->addSubTab('amd', $this->lng->txt('md_advanced'), $this->ctrl->getLinkTarget($this, 'editAMD'));
        }
        $this->tabs->setSubTabActive($active_tab_id);
    }


    /**
     * Display form for editing custom icons
     */
    protected function editCustomIcons()
    {
        $form = new ilOrgUnitTypeCustomIconsFormGUI($this, new ilOrgUnitType((int) $_GET['type_id']));
        $this->tpl->setContent($form->getHTML());
    }


    /**
     * Save icon
     */
    protected function updateCustomIcons()
    {
        $form = new ilOrgUnitTypeCustomIconsFormGUI($this, new ilOrgUnitType((int) $_GET['type_id']));
        if ($form->saveObject()) {
            ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this);
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }


    protected function editAMD()
    {
        $form = new ilOrgUnitTypeAdvancedMetaDataFormGUI($this, new ilOrgUnitType((int) $_GET['type_id']));
        $this->tpl->setContent($form->getHTML());
    }


    protected function updateAMD()
    {
        $form = new ilOrgUnitTypeAdvancedMetaDataFormGUI($this, new ilOrgUnitType((int) $_GET['type_id']));
        if ($form->saveObject()) {
            ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this);
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }


    /**
     * Display all types in a table with actions to edit/delete
     */
    protected function listTypes()
    {
        $button = ilLinkButton::getInstance();
        $button->setCaption('orgu_type_add');
        $button->setUrl($this->ctrl->getLinkTarget($this, 'add'));
        $this->toolbar->addButtonInstance($button);

        $table = new ilOrgUnitTypeTableGUI($this, 'listTypes');
        $this->tpl->setContent($table->getHTML());
    }


    /**
     * Display form to create a new OrgUnit type
     */
    protected function add()
    {
        $form = new ilOrgUnitTypeFormGUI($this, new ilOrgUnitType());
        $this->tpl->setContent($form->getHTML());
    }


    /**
     * Display form to edit an existing OrgUnit type
     */
    protected function edit()
    {
        $type = new ilOrgUnitType((int) $_GET['type_id']);
        $form = new ilOrgUnitTypeFormGUI($this, $type);
        $this->tpl->setContent($form->getHTML());
    }


    /**
     * Create (save) type
     */
    protected function create()
    {
        $form = new ilOrgUnitTypeFormGUI($this, new ilOrgUnitType());
        if ($form->saveObject()) {
            ilUtil::sendSuccess($this->lng->txt('msg_obj_created'), true);
            $this->ctrl->redirect($this);
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }


    /**
     * Update (save) type
     */
    protected function update()
    {
        $form = new ilOrgUnitTypeFormGUI($this, new ilOrgUnitType((int) $_GET['type_id']));
        if ($form->saveObject()) {
            ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this);
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }


    /**
     * Delete a type
     */
    protected function delete()
    {
        $type = new ilOrgUnitType((int) $_GET['type_id']);
        try {
            $type->delete();
            ilUtil::sendSuccess($this->lng->txt('orgu_type_msg_deleted'), true);
            $this->ctrl->redirect($this);
        } catch (ilException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->redirect($this);
        }
    }
}
