<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* @author Stefan Meyer <meyer@leifos.com>
*/
abstract class ilRemoteObjectBaseGUI extends ilObject2GUI
{
    private ilLogger $logger;

    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC;

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->logger = $DIC->logger()->wsrv();

        $this->lng->loadLanguageModule('ecs');
    }
    
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();
        $this->logger->info("Can write:" . print_r($this->checkPermissionBool('write'), true));

        switch ($next_class) {
            case 'ilinfoscreengui':
                // forwards command
                $this->infoScreen();
                break;
        
            case 'ilpermissiongui':
                $this->tabs_gui->activateTab('id_permissions');
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                break;
            
            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ilECSUserConsentModalGUI::class):
                $consent_gui = new ilECSUserConsentModalGUI(
                    $this->user->getId(),
                    $this->ref_id
                );
                $this->ctrl->setReturn($this, 'call');
                $this->ctrl->forwardCommand($consent_gui);
                break;

            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = "infoScreen";
                }
                $cmd .= "Object";
                $this->logger->info("cmd before call:" . print_r($cmd, true));
                $this->$cmd();
                break;
        }
        $this->logger->info("cmd:" . print_r($cmd, true));
    }
    
    /**
     * show remote object
     */
    public function showObject() : void
    {
        if ($this->user->getId() === ANONYMOUS_USER_ID ||
            $this->object->isLocalObject()) {
            $this->ctrl->redirectToURL($this->object->getRemoteLink());
        } else {
            $link = $this->object->getFullRemoteLink();
            $this->ctrl->redirectToURL($link);
        }
    }
    
    /**
     * get tabs
     */
    protected function setTabs() : void
    {
        if ($this->checkPermissionBool('visible')) {
            $this->tabs_gui->addTab(
                "info",
                $this->lng->txt("info_short"),
                $this->ctrl->getLinkTarget($this, "infoScreen")
            );
        }

        if ($this->checkPermissionBool('write')) {
            $this->tabs_gui->addTab(
                "edit",
                $this->lng->txt("edit"),
                $this->ctrl->getLinkTarget($this, "edit")
            );
        }
        
        // will add permissions if needed
        parent::setTabs();
    }
    
    /**
     * call remote object
     *
     * @return bool
     */
    public function callObject() : bool
    {
        ilChangeEvent::_recordReadEvent(
            $this->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $this->user->getId()
        );
                

        // check if the assigned object is hosted on the same installation
        $link = $this->object->getFullRemoteLink();
        if ($link) {
            $this->ctrl->redirectToURL($link);
            return true;
        }

        $this->tpl->setOnScreenMessage('failure', 'Cannot call remote object.');
        $this->infoScreenObject();
        return false;
    }
    
    /**
    * this one is called from the info button in the repository
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function infoScreenObject() : void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }
    
    /**
     * show info screen
     */
    public function infoScreen() : void
    {
        if (!$this->access->checkAccess("visible", "", $this->object->getRefId())) {
            $this->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->error->MESSAGE
            );
        }

        $this->ctrl->setReturn($this, 'call');
        $consent_gui = new ilECSUserConsentModalGUI(
            $this->user->getId(),
            $this->ref_id,
            $this
        );
        $consent_gui->addLinkToToolbar($this->toolbar);

        $this->tabs_gui->activateTab('info');

        $info = new ilInfoScreenGUI($this);

        $info->addSection($this->lng->txt('ecs_general_info'));
        $info->addProperty($this->lng->txt('title'), $this->object->getTitle());
        if ($this->object->getOrganization()) {
            $info->addProperty($this->lng->txt('organization'), $this->object->getOrganization());
        }
        if ($this->object->getDescription()) {
            $info->addProperty($this->lng->txt('description'), $this->object->getDescription());
        }
        if ($this->object->getLocalInformation()) {
            $info->addProperty($this->lng->txt('ecs_local_information'), $this->object->getLocalInformation());
        }
        
        $this->addCustomInfoFields($info);
                
        $record_gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_INFO,
            $this->getType(),
            $this->object->getId()
        );
        $record_gui->setInfoObject($info);
        $record_gui->parse();
        
        $this->ctrl->forwardCommand($info);
    }
    
    /**
     * Add custom fields to info screen
     *
     * @param ilInfoScreenGUI $a_info
     */
    protected function addCustomInfoFields(ilInfoScreenGUI $a_info) : void
    {
        // can be overwritten by subclasses
    }
    
    /**
     * Edit settings
     */
    public function editObject(ilPropertyFormGUI $form = null) : void
    {
        if (!$this->access->checkAccess("write", "", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }
        $this->logger->info("Can write:" . print_r($this->checkPermissionBool('write'), true));
        $this->tabs_gui->activateTab('edit');
        
        if (!$form) {
            $form = $this->initEditForm();
        }
        $this->tpl->setContent($form->getHTML());
    }
    
    /**
     * Init edit settings form
     */
    protected function initEditForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('ecs_general_info'));
        $form->addCommandButton('update', $this->lng->txt('save'));
        $form->addCommandButton('edit', $this->lng->txt('cancel'));
        
        $text = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $text->setValue($this->object->getTitle());
        $text->setSize(min(40, ilObject::TITLE_LENGTH));
        $text->setMaxLength(ilObject::TITLE_LENGTH);
        $text->setDisabled(true);
        $form->addItem($text);

        $area = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
        $area->setValue($this->object->getDescription());
        $area->setRows(3);
        $area->setCols(80);
        $area->setDisabled(true);
        $form->addItem($area);
        
        $area = new ilTextAreaInputGUI($this->lng->txt('ecs_local_information'), 'local_info');
        $area->setValue($this->object->getLocalInformation());
        $area->setRows(3);
        $area->setCols(80);
        $form->addItem($area);
        
        $this->addCustomEditForm($form);
        
        $record_gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_EDITOR,
            $this->getType(),
            $this->object->getId()
        );
        $record_gui->setPropertyForm($form);
        $record_gui->parse();
        
        return $form;
    }
    
    /**
     * Add custom fields to edit form
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function addCustomEditForm(ilPropertyFormGUI $a_form) : void
    {
    }

    public function updateObject() : void
    {
        if (!$this->checkPermissionBool('write')) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->MESSAGE);
        }
        
        $form = $this->initEditForm();
        if ($form->checkInput()) {
            $this->object->setLocalInformation($form->getInput('local_info'));
            
            $this->updateCustomValues($form);
                    
            $this->object->update();

            // Save advanced meta data
            $record_gui = new ilAdvancedMDRecordGUI(
                ilAdvancedMDRecordGUI::MODE_EDITOR,
                $this->getType(),
                $this->object->getId()
            );
            $record_gui->loadFromPost();// TODO PHP8-REVIEW Undefined method
            $record_gui->saveValues();// TODO PHP8-REVIEW Undefined method
            
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"));
            $this->editObject();
        }
        
        $form->setValuesByPost();
        $this->editObject($form);
    }
    
    /**
     * Update object custom values
     *
     * @param ilPropertyFormGUI $a_form
     */
    protected function updateCustomValues(ilPropertyFormGUI $a_form) : void
    {
    }

    public static function _goto(string $a_target) : void
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();
        if ($ilAccess->checkAccess("read", "", (int) $a_target)) {
            ilObjectGUI::_gotoRepositoryNode((int) $a_target);
        }
        
        if ($ilAccess->checkAccess("visible", "", (int) $a_target)) {
            ilObjectGUI::_gotoRepositoryNode((int) $a_target, "infoScreen");
        }
        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }
}
