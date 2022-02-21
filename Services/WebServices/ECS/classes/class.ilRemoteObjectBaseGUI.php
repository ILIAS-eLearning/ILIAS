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
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ServicesWebServicesECS
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
    
    public function executeCommand()
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

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "editSettings";
                }
                $cmd .= "Object";
                $this->logger->info("cmd before call:" . print_r($cmd, true));
                $this->$cmd();
                break;
        }
        $this->logger->info("cmd:" . print_r($cmd, true));

        return true;
    }
    
    /**
     * show remote object
     */
    public function showObject()
    {
        if ($this->user->getId() == ANONYMOUS_USER_ID ||
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
    public function setTabs()
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
    public function callObject()
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
        } else {
            $this->tpl->setOnScreenMessage('failure', 'Cannot call remote object.');
            $this->infoScreenObject();
            return false;
        }
    }
    
    /**
    * this one is called from the info button in the repository
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function infoScreenObject()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }
    
    /**
     * show info screen
     */
    public function infoScreen()
    {
        if (!$this->access->checkAccess("visible", "", $this->object->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilErr->MESSAGE);
        }
        
        $this->tabs_gui->activateTab('info');

        $info = new ilInfoScreenGUI($this);
    
        if ($this->user->getId() == ANONYMOUS_USER_ID ||
            $this->object->isLocalObject()) {
            $info->addButton(
                $this->lng->txt($this->getType() . '_call'),
                $this->object->getRemoteLink(),
                'target="_blank"'
            );
        } else {
            $info->addButton(
                $this->lng->txt($this->getType() . '_call'),
                $this->ctrl->getLinkTarget($this, 'call'),
                'target="_blank"'
            );
        }
        
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
    protected function addCustomInfoFields(ilInfoScreenGUI $a_info)
    {
    }
    
    /**
     * Edit settings
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function editObject(ilPropertyFormGUI $a_form = null)
    {
        if (!$this->access->checkAccess("write", "", $this->object->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilErr->MESSAGE);
        }
        $this->logger->info("Can write:" . print_r($this->checkPermissionBool('write'), true));
        $this->tabs_gui->activateTab('edit');
        
        if (!$a_form) {
            $a_form = $this->initEditForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    /**
     * Init edit settings form
     *
     * @return ilPropertyFormGUI
     */
    protected function initEditForm()
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
        $area->setValue(strval($this->object->getLocalInformation()));
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
    protected function addCustomEditForm(ilPropertyFormGUI $a_form)
    {
    }

    /**
     * update object
     */
    public function updateObject()
    {
        if (!$this->checkPermissionBool('write')) {
            $this->ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $this->ilErr->MESSAGE);
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
            $record_gui->loadFromPost();
            $record_gui->saveValues();
            
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
    protected function updateCustomValues(ilPropertyFormGUI $a_form)
    {
    }
    
    /**
    * redirect script
    *
    * @param string $a_target
    */
    public static function _goto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();
        if ($ilAccess->checkAccess("read", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target);
        }
        
        if ($ilAccess->checkAccess("visible", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        }
        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }
}
