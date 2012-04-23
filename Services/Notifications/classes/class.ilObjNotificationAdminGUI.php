<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";
require_once "./Services/Notifications/classes/class.ilObjNotificationAdmin.php";
require_once "./Services/Notifications/classes/class.ilObjNotificationAdminAccess.php";

/**
* GUI class for notification objects.
*
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjNotificationAdminGUI: ilPermissionGUI
* @ilCtrl_IsCalledBy ilObjNotificationAdminGUI: ilAdministrationGUI
*
* @ingroup ServicesNotifications
*/
class ilObjNotificationAdminGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access	public
	*/
	function __construct($a_data,$a_id,$a_call_by_reference = true, $a_prepare_output = true) {
		$this->type = "nota";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
                $this->lng->loadLanguageModule('notification');
	}
	
	function _forwards()
	{
		return array();
	}
	
	function executeCommand()
	{
		global $ilAccess, $ilNavigationHistory, $ilCtrl, $ilUser, $ilTabs;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
	
		$this->prepareOutput();

		switch ($next_class)
		{
			case 'ilpermissiongui':
				//$ilTabs->activateTab("id_permissions");
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

                        default:
				$this->__initSubTabs();
                                $ilTabs->activateTab("view");
                            
				if (empty($cmd) || $cmd == 'view') {
                                    $cmd = 'showTypes';
				}

				$cmd .= "Object";
				$this->$cmd();
				break;
		}		
	}

	/**
	* save object
	*
	* @access	public
	*/
	static function saveObject2($params = array()) {
		global $objDefinition, $ilUser;

                // create and insert file in grp_tree
                require_once 'Services/Notifications/classes/class.ilObjNotificationAdmin.php';
                $fileObj = new ilObjNotificationAdmin();
                $fileObj->setTitle('notification admin');
                $fileObj->create();
                $fileObj->createReference();
                $fileObj->putInTree(SYSTEM_FOLDER_ID);
                //$fileObj->setPermissions($params['ref_id']);
                // upload file to filesystem
	}

	function setTabs() {
		global $ilAccess, $ilTabs, $lng;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$ilTabs->addTab("id_info",
				$lng->txt("info_short"),
				$this->ctrl->getLinkTargetByClass(array("ilobjfilegui", "ilinfoscreengui"), "showSummary"));
		}

		if ($ilAccess->checkAccess("edit_permission", "", $this->ref_id))
		{
				$ilTabs->addTab("id_permissions",
				$lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"));
		}
	}

	// init sub tabs
	function __initSubTabs() {
		global $ilTabs, $ilSetting, $ilCtrl;

                $ilTabs->addSubTabTarget("notification_general", $this->ctrl->getLinkTargetByClass('ilObjNotificationAdminGUI', "showGeneralSettings"));
                //$ilTabs->addSubTabTarget("notification_admin_channels", $this->ctrl->getLinkTargetByClass('ilObjNotificationAdminGUI', "showChannels"));
		$ilTabs->addSubTabTarget("notification_admin_types", $this->ctrl->getLinkTargetByClass('ilObjNotificationAdminGUI', "showTypes"));
                $ilTabs->addSubTabTarget("notification_admin_matrix", $this->ctrl->getLinkTargetByClass('ilObjNotificationAdminGUI', "showConfigMatrix"));
	}

	function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object)) {
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $_GET["ref_id"]);
		}
	}

        function showGeneralSettingsObject($form = null) {
            require_once 'Services/Notifications/classes/class.ilNotificationAdminSettingsForm.php';

            if ($form == null) {
                $form = ilNotificationAdminSettingsForm::getGeneralSettingsForm();
                $settings = new ilSetting('notifications');

                /**
                 * @todo dirty...
				 * 
				 * push all notifiation settings to the form to enable custom
				 * settings per channel
                 */
                $form->setValuesByArray(array_merge($settings->getAll(), $form->restored_values));
            }

            $form->setFormAction($this->ctrl->getFormAction($this, 'saveGeneralSettings'));
            $form->addCommandButton('saveGeneralSettings', 'save');
            $form->addCommandButton('showGeneralSettings', 'cancel');

            $this->tpl->setContent($form->getHtml());
        }

        function saveGeneralSettingsObject() {
            require_once 'Services/Notifications/classes/class.ilNotificationAdminSettingsForm.php';
            #require_once 'Services/Administration/classes/class.ilSetting.php';

            $settings = new ilSetting('notifications');

            $form = ilNotificationAdminSettingsForm::getGeneralSettingsForm();
            $form->setValuesByPost();
            if (!$form->checkInput()) {
                $this->showGeneralSettingsObject($form);
            }
            else {
                /**
                 * @todo dirty...
				 * 
				 * push all notifiation settings to the form to enable custom
				 * settings per channel
                 */
                $values = $form->store_values;//array('enable_osd', 'osd_polling_intervall', 'enable_mail');
                
				// handle custom channel settings
                foreach($values as $v) {
                    $settings->set($v, $_POST[$v]);
                }

                foreach($_REQUEST['notifications'] as $type => $value) {
                    ilNotificationDatabaseHandler::setConfigTypeForChannel($type, $value);
                }

                $this->showGeneralSettingsObject();
            }

        }

        public function showTypesObject() {
            global $ilTabs;
            
            $ilTabs->activateSubTab('notification_admin_types');

            require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
            require_once 'Services/Notifications/classes/class.ilNotificationAdminSettingsForm.php';
            
            $form = ilNotificationAdminSettingsForm::getTypeForm(ilNotificationDatabaseHandler::getAvailableTypes());
            $form->setFormAction($this->ctrl->getFormAction($this, 'showTypes'));
            $form->addCommandButton('saveTypes', $this->lng->txt('save'));
            $form->addCommandButton('showTypes', $this->lng->txt('cancel'));
            $this->tpl->setContent($form->getHtml());

        }

        public function showChannelsObject() {
            global $ilTabs;

            $ilTabs->activateSubTab('notification_admin_channels');

            require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
            require_once 'Services/Notifications/classes/class.ilNotificationAdminSettingsForm.php';

            $form = ilNotificationAdminSettingsForm::getChannelForm(ilNotificationDatabaseHandler::getAvailableChannels());
            $form->setFormAction($this->ctrl->getFormAction($this, 'showChannels'));
            $form->addCommandButton('saveChannels', $this->lng->txt('save'));
            $form->addCommandButton('showChannels', $this->lng->txt('cancel'));
            $this->tpl->setContent($form->getHtml());

        }

        public function saveTypesObject() {
            require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
            foreach($_REQUEST['notifications'] as $type => $value) {
                ilNotificationDatabaseHandler::setConfigTypeForType($type, $value);
            }
            $this->showTypesObject();
        }

        public function saveChannelsObject() {
            require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
            foreach($_REQUEST['notifications'] as $type => $value) {
                ilNotificationDatabaseHandler::setConfigTypeForChannel($type, $value);
            }
            $this->showChannelsObject();
        }

        public function showConfigMatrixObject() {
            global $ilTabs;

            $ilTabs->activateSubTab('notification_admin_matrix');

            require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
            require_once 'Services/Notifications/classes/class.ilNotificationSettingsTable.php';

            global $ilCtrl, $lng;

            $userdata = ilNotificationDatabaseHandler::loadUserConfig(-1);

            $table = new ilNotificationSettingsTable($this, 'a title', ilNotificationDatabaseHandler::getAvailableChannels(), $userdata, true);
            $table->setFormAction($ilCtrl->getFormAction($this, 'saveConfigMatrix'));
            $table->setData(ilNotificationDatabaseHandler::getAvailableTypes());
            $table->setDescription($lng->txt('notification_admin_matrix_settings_table_desc'));
            $table->addCommandButton('saveConfigMatrix', $lng->txt('save'));
            $table->addCommandButton('showConfigMatrix', $lng->txt('cancel'));

            $this->tpl->setContent($table->getHtml());
        }

        private function saveConfigMatrixObject() {
            global $ilUser, $ilCtrl;
            require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';

            ilNotificationDatabaseHandler::setUserConfig(-1, $_REQUEST['notification'] ? $_REQUEST['notification'] : array());
            $this->showConfigMatrixObject();
        }
} // END class.ilObjFileGUI
?>
