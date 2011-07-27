<?php

class ilNotificationSystem {

    private static $instance;

    private $handler = array();

    private $defaultLanguage = 'en';

    private function  __construct() {
        require_once 'Services/Notifications/classes/class.ilNotificationEchoHandler.php';
        require_once 'Services/Notifications/classes/class.ilNotificationOSDHandler.php';
        require_once 'Services/Notifications/classes/class.ilNotificationMailHandler.php';

        $this->addHandler('echo', new ilNotificationEchoHandler());
        $this->addHandler('osd', new ilNotificationOSDHandler());
        $this->addHandler('mail', new ilNotificationMailHandler());

    }

    private static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function addHandler($channel, ilNotificationHandler $handler) {
        if (!array_key_exists($channel, $this->handler) || !is_array($this->handler[$channel]))
            $this->handler[$channel] = array();

        $this->handler[$channel][] = $handler;
    }

    private function toUsers(ilNotificationConfig $notification, $users, $processAsync = false) {
        
        require_once 'Services/Notifications/classes/class.ilNotificationUserIterator.php';
        require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';


        if ($processAsync == false) {

            $adminConfig = ilNotificationDatabaseHandler::loadUserConfig(-1);
            $usersWithCustomConfig = ilNotificationDatabaseHandler::getUsersWithCustomConfig($users);

            foreach($users as $user_id) {
                if ($usersWithCustomConfig[$user_id]) {
/** @todo was ist hier? **/
                }
            }

            $channels = ilNotificationDatabaseHandler::getAvailableChannels();
            $types = ilNotificationDatabaseHandler::getAvailableTypes();

            $lang = ilNotificationDatabaseHandler::getLanguageVars($notification->getLanguageParameters());

            $user_by_handler = array();

            if ($types[$notification->getType()]['config_type'] == 'set_by_user') {
                $it = new ilNotificationUserIterator($notification->getType(), $users);
                
                $channelsByAdmin = false;

                foreach($it as $usr_id => $data) {
                    if (!$channels[$data['channel']])
                        continue;
                    
                    if (!$user_by_handler[$data['channel']])
                        $user_by_handler[$data['channel']] = array();

                    $user_by_handler[$data['channel']][] = $usr_id;
                }
            }
            else if ($types[$notification->getType()]['config_type'] != 'disabled') {
                $channelsByAdmin = true;
                //$user_by_handler = array();

		if (isset($adminConfig[$notification->getType()])) {
			
			foreach($adminConfig[$notification->getType()] as $channel) {
			    if (!$channels[$channel])
				continue;
			    $user_by_handler[$channel] = $users;

			}
		}
            }


            $userCache = array();

            foreach($user_by_handler as $handler => $users) {
                $handler = $this->handler[$handler];
                foreach($users as $userId) {
                    if (!$userCache[$userId]) {
                        $userCache[$userId] = new ilObjUser($userId);
                    }
                    $user = $userCache[$userId];

                    $instance = $notification->getUserInstance($user, $lang, $this->defaultLanguage);
                    foreach($handler as $h) {
                        $h->notify($instance);
                    }
                }
            }
        }
        else {
            ilNotificationDatabaseHandler::enqueueByUsers($notification, $users);
        }
    }

    private function toListeners(ilNotificationConfig $notification, $ref_id, $processAsync = false) {
        require_once 'Services/Notifications/classes/class.ilNotificationUserIterator.php';
        require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';

        if ($processAsync == false) {
            $users = ilNotificationDatabaseHandler::getUsersByListener($notification->getType(), $ref_id);
            self::toUsers($notification, $users, false);
            if ($notification->hasDisableAfterDeliverySet()) {
                ilNotificationDatabaseHandler::disableListeners($notification->getType(), $ref_id);
            }
        }
        else {
            ilNotificationDatabaseHandler::enqueueByListener($notification, $ref_id);
        }
    }

    private function toRoles(ilNotificationConfig $notification, array $roles, $processAsync = false) {
        require_once 'Services/Notifications/classes/class.ilNotificationUserIterator.php';
        require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
        
        global $rbacreview;

        $users = array();
        foreach($roles as $role) {
            $users[] = $rbacreview->assignedUsers($role);
        }
        $users = array_unique(call_user_func_array('array_merge', $users));

        self::toUsers($notification, $users, $processAsync);
    }


    public static function sendNotificationToUsers(ilNotificationConfig $notification, $users, $processAsync = false) {
        self::getInstance()->toUsers($notification, $users, $processAsync);
    }

    public static function sendNotificationToListeners(ilNotificationConfig $notification, $ref_id, $processAsync = false) {
        self::getInstance()->toListeners($notification, $ref_id, $processAsync);
    }

    public static function sendNotificationToRoles(ilNotificationConfig $notification, array $roles, $processAsync = false) {
        self::getInstance()->toRoles($notification, $roles, $processAsync);
    }

    public static function enableListeners($module, $ref_id) {
        require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
        ilNotificationDatabaseHandler::enableListeners($module, $ref_id);
    }

    public static function enableUserListeners($module, $ref_id, array $users) {
        if (!$users)
            return;
        require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
        ilNotificationDatabaseHandler::enableListeners($module, $ref_id, $users);
    }
}
?>
