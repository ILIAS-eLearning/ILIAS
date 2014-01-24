<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Object/classes/class.ilObjectGUI.php';
require_once 'Services/Notifications/classes/class.ilNotificationConfig.php';
require_once 'Services/Notifications/classes/class.ilNotificationHandler.php';

/**
*
* @author Jan Posselt <jposselt@databay.de.de>
* @version $Id$
*
* @ilCtrl_Calls ilNotificationGUI:
* @ilCtrl_IsCalledBy ilNotificationGUI: ilPersonalProfileGUI, ilPersonalDesktopGUI
*
* @ingroup ServicesNotifications
*/
class ilNotificationGUI {

        private $handler = array();

	/**
	* Constructor
	* @access	public
	*/
	function __construct() {
		$this->type = "not";

                require_once 'Services/Notifications/classes/class.ilNotificationSetupHelper.php';
	}
	
	function _forwards() {
		return array();
	}
	
	function executeCommand() {
		global $ilCtrl;

                if (!$ilCtrl->getCmd())
                    return;

                $cmd = $ilCtrl->getCmd() . 'Object';
                $this->$cmd();

	}

        public function getHandler($type) {
            return $this->handler[$type];
        }

        private function getAvailableTypes($types = array()) {
            return ilNotificationDatabaseHandler::getAvailableTypes($types);
        }

        private function getAvailableChannels($types = array()) {
            return ilNotificationDatabaseHandler::getAvailableChannels($types);
        }

		/**
		 * Returns the pending on screen notifications for a user request
		 * 
		 * @todo this method should move to a better place as it handels channel
		 *       sprecific things.
		 * 
		 * @global ilUser $ilUser
		 * @return string
		 */
        public function getOSDNotificationsObject() {
            global $ilUser;

            if ($ilUser->getId() == ANONYMOUS_USER_ID) {
                return '{}';
            }

	    $GLOBALS['WEB_ACCESS_WITHOUT_SESSION'] = true;

            require_once 'Services/Notifications/classes/class.ilNotificationEchoHandler.php';
            require_once 'Services/Notifications/classes/class.ilNotificationOSDHandler.php';
            
            $notifications = ilNotificationOSDHandler::getNotificationsForUser($ilUser->getId(), true, (int)$_REQUEST['max_age']);
            $result = new stdClass();
            $result->notifications = $notifications;
            $result->server_time = time();
            echo json_encode($result);
            exit;
        }

        public function removeOSDNotificationsObject() {
            global $ilUser;

	    $GLOBALS['WEB_ACCESS_WITHOUT_SESSION'] = true;

            require_once 'Services/Notifications/classes/class.ilNotificationEchoHandler.php';
            require_once 'Services/Notifications/classes/class.ilNotificationOSDHandler.php';

            ilNotificationOSDHandler::removeNotification($_REQUEST['notification_id']);

            exit;
        }

        public function addHandler($channel, ilNotificationHandler $handler) {
            if (!array_key_exists($channel, $this->handler) || !is_array($this->handler[$channel]))
                $this->handler[$channel] = array();
            
            $this->handler[$channel][] = $handler;
        }

        private function saveCustomizingOptionObject() {
            global $ilUser;

            if ($_POST['enable_custom_notification_configuration']) {
                $ilUser->writePref('use_custom_notification_setting', 1);
            }
            else {
                $ilUser->writePref('use_custom_notification_setting', 0);
            }


            $this->showSettingsObject();
        }

        public function showSettingsObject() {
            global $tpl, $ilCtrl, $ilUser, $lng;

            require_once 'Services/Notifications/classes/class.ilNotificationSettingsTable.php';
            require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';

            $userTypes = ilNotificationDatabaseHandler::loadUserConfig($ilUser->getId());

            $lng->loadLanguageModule('notification');
            
            require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
            $form = new ilPropertyFormGUI();
            $chk = new ilCheckboxInputGUI($lng->txt('enable_custom_notification_configuration'), 'enable_custom_notification_configuration');
            $chk->setValue('1');
            $chk->setChecked($ilUser->getPref('use_custom_notification_setting') == 1);
            $form->addItem($chk);

            $form->setFormAction($ilCtrl->getFormAction($this, 'showSettingsObject'));
            $form->addCommandButton('saveCustomizingOption', $lng->txt('save'));
            $form->addCommandButton('showSettings', $lng->txt('cancel'));

            $table = new ilNotificationSettingsTable($this, 'a title', $this->getAvailableChannels(array('set_by_user')), $userTypes);

            $table->setFormAction($ilCtrl->getFormAction($this, 'saveSettings'));
            $table->setData($this->getAvailableTypes(array('set_by_user')));

            if ($ilUser->getPref('use_custom_notification_setting') == 1) {
                $table->addCommandButton('saveSettings', $lng->txt('save'));
                $table->addCommandButton('showSettings', $lng->txt('cancel'));
                $table->setEditable(true);
            }
            else {
                $table->setEditable(false);
            }

            $tpl->setContent($form->getHtml() . $table->getHTML());
        }

        private function saveSettingsObject() {
            global $ilUser, $ilCtrl;
            require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';

            ilNotificationDatabaseHandler::setUserConfig($ilUser->getId(), $_REQUEST['notification'] ? $_REQUEST['notification'] : array());
            $this->showSettingsObject();
        }

	function addLocatorItems() {
		global $ilLocator, $ilCtrl;
		
		if (is_object($this->object)) {
                    $ilLocator->addItem($this->object->getTitle(), $ilCtrl->getLinkTarget($this, ''), '', $_GET["ref_id"]);
		}
	}

} // END class.ilObjFileGUI
?>
