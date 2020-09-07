<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/Object/classes/class.ilObject2GUI.php');

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ServicesWebServicesECS
*/
abstract class ilRemoteObjectBaseGUI extends ilObject2GUI
{
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->lng->loadLanguageModule('ecs');
    }
    
    public function executeCommand()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        switch ($next_class) {
            case 'ilinfoscreengui':
                // forwards command
                $this->infoScreen();
                break;
        
            case 'ilpermissiongui':
                $ilTabs->activateTab('id_permissions');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                break;
            
            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "editSettings";
                }
                $cmd .= "Object";
                $this->$cmd();
                break;
        }
        return true;
    }
    
    /**
     * show remote object
     */
    public function showObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        if ($ilUser->getId() == ANONYMOUS_USER_ID ||
            $this->object->isLocalObject()) {
            ilUtil::redirect($this->object->getRemoteLink());
        } else {
            $link = $this->object->getFullRemoteLink();
            ilUtil::redirect($link);
        }
    }
    
    /**
     * get tabs
     */
    public function setTabs()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        
        if ($this->checkPermissionBool('visible')) {
            $ilTabs->addTab(
                "info",
                $this->lng->txt("info_short"),
                $this->ctrl->getLinkTarget($this, "infoScreen")
            );
        }

        if ($this->checkPermissionBool('write')) {
            $ilTabs->addTab(
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
        include_once './Services/Tracking/classes/class.ilChangeEvent.php';
        ilChangeEvent::_recordReadEvent(
            $this->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $GLOBALS['DIC']['ilUser']->getId()
        );
                

        // check if the assigned object is hosted on the same installation
        $link = $this->object->getFullRemoteLink();
        if ($link) {
            ilUtil::redirect($link);
            return true;
        } else {
            ilUtil::sendFailure('Cannot call remote object.');
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
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilUser = $DIC['ilUser'];
        $ilTabs = $DIC['ilTabs'];
        
        if (!$this->checkPermissionBool('visible')) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->MESSAGE);
        }
        
        $ilTabs->activateTab('info');

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
    
        if ($ilUser->getId() == ANONYMOUS_USER_ID ||
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
        if (strlen($this->object->getOrganization())) {
            $info->addProperty($this->lng->txt('organization'), $this->object->getOrganization());
        }
        if (strlen($this->object->getDescription())) {
            $info->addProperty($this->lng->txt('description'), $this->object->getDescription());
        }
        if (strlen($loc = $this->object->getLocalInformation())) {
            $info->addProperty($this->lng->txt('ecs_local_information'), $this->object->getLocalInformation());
        }
        
        $this->addCustomInfoFields($info);
                
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
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
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilTabs = $DIC['ilTabs'];

        if (!$this->checkPermissionBool('write')) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->MESSAGE);
        }
        
        $ilTabs->activateTab('edit');
        
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
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
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
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
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
        global $DIC;

        $ilErr = $DIC['ilErr'];
                
        if (!$this->checkPermissionBool('write')) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->MESSAGE);
        }
        
        $form = $this->initEditForm();
        if ($form->checkInput()) {
            $this->object->setLocalInformation($a_form->getInput('local_info'));
            
            $this->updateCustomValues($form);
                    
            $this->object->update();

            // Save advanced meta data
            include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
            $record_gui = new ilAdvancedMDRecordGUI(
                ilAdvancedMDRecordGUI::MODE_EDITOR,
                $this->getType(),
                $this->object->getId()
            );
            $record_gui->loadFromPost();
            $record_gui->saveValues();
            
            ilUtil::sendSuccess($this->lng->txt("settings_saved"));
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

        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];

        //static if ($this->checkPermissionBool("visible", "", "", $a_target))
        if ($ilAccess->checkAccess('visible', '', $a_target)) {
            $_GET["cmd"] = "infoScreen";
            $_GET["ref_id"] = $a_target;
            $_GET["baseClass"] = "ilRepositoryGUI";
            include("ilias.php");
            exit;
        }
        //static else if ($this->checkPermissionBool("read", "", "", ROOT_FOLDER_ID))
        if ($ilAccess->checkAccess('read', '', ROOT_FOLDER_ID)) {
            $_GET["cmd"] = "frameset";
            $_GET["target"] = "";
            $_GET["ref_id"] = ROOT_FOLDER_ID;
            ilUtil::sendFailure(sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            $_GET["baseClass"] = "ilRepositoryGUI";
            include("ilias.php");
            exit;
        }
        
        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }
}
