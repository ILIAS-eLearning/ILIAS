<?php
/**
 * Main notification handling routines for sending notifications to
 * recipients.
 *
 * Recipients may be
 * <ul>
 *	<li>a list of user ids</li>
 *  <li>roles</li>
 *  <li>users which registered a listener to an ref_id</li>
 * </ul>
 */
class ilNotificationSystem
{
    private static $instance;

    private $handler = array();

    private $defaultLanguage = 'en';

    private $rbacReview;

    private function __construct(\ilRbacReview $rbacReview = null)
    {
        require_once 'Services/Notifications/classes/class.ilNotificationEchoHandler.php';
        require_once 'Services/Notifications/classes/class.ilNotificationOSDHandler.php';
        require_once 'Services/Notifications/classes/class.ilNotificationMailHandler.php';

        // add default handlers
        $this->addHandler('echo', new ilNotificationEchoHandler());
        $this->addHandler('osd', new ilNotificationOSDHandler());
        $this->addHandler('mail', new ilNotificationMailHandler());

        if ($rbacReview === null) {
            global $DIC;
            $rbacReview = $DIC->rbac()->review();
        }
        $this->rbacReview = $rbacReview;
    }

    private static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Registers a new handler for the given channel name
     *
     * @param string $channel
     * @param ilNotificationHandler $handler
     */
    private function addHandler($channel, ilNotificationHandler $handler)
    {
        if (!array_key_exists($channel, $this->handler) || !is_array($this->handler[$channel])) {
            $this->handler[$channel] = array();
        }

        $this->handler[$channel][] = $handler;
    }

    /**
     * Creates the user notifications and send them. If processAsync is true
     * the notifications will be serialized and persisted to the database
     *
     * @param ilNotificationConfig $notification
     * @param type $users
     * @param type $processAsync
     */
    private function toUsers(ilNotificationConfig $notification, $users, $processAsync = false)
    {
        require_once 'Services/Notifications/classes/class.ilNotificationUserIterator.php';
        require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';


        // if async processing is disabled send them immediately
        if ($processAsync == false) {

            // loading the default configuration
            $adminConfig = ilNotificationDatabaseHandler::loadUserConfig(-1);
            $usersWithCustomConfig = ilNotificationDatabaseHandler::getUsersWithCustomConfig($users);

            // @todo this loop might be obsolet :)
            foreach ($users as $user_id) {
                if ($usersWithCustomConfig[$user_id]) {
                    /** @todo was ist hier? **/
                }
            }

            // load all available channels
            $channels = ilNotificationDatabaseHandler::getAvailableChannels();
            // load all available types
            $types = ilNotificationDatabaseHandler::getAvailableTypes();
            // preload translation vars
            $lang = ilNotificationDatabaseHandler::getTranslatedLanguageVariablesOfNotificationParameters($notification->getLanguageParameters());

            $user_by_handler = array();

            // check if the type allows custom user configurations for determining
            // the output channel (e.g. send chat notifications only via osd)
            if ($types[$notification->getType()]['config_type'] == 'set_by_user') {
                $it = new ilNotificationUserIterator($notification->getType(), $users);
                
                $channelsByAdmin = false;

                // add the user to each channel he configured in his own configuration
                foreach ($it as $usr_id => $data) {
                    // the configured user channel is (currently) not known
                    if (!$channels[$data['channel']]) {
                        continue;
                    }
                    
                    if (!$user_by_handler[$data['channel']]) {
                        $user_by_handler[$data['channel']] = array();
                    }

                    $user_by_handler[$data['channel']][] = $usr_id;
                }
            }
            // if type is configured to allow settings only applied by admin
            elseif ($types[$notification->getType()]['config_type'] != 'disabled') {
                $channelsByAdmin = true;
                //$user_by_handler = array();

                if (isset($adminConfig[$notification->getType()])) {
                    foreach ($adminConfig[$notification->getType()] as $channel) {
                        if (!$channels[$channel]) {
                            continue;
                        }
                        $user_by_handler[$channel] = $users;
                    }
                }
            }


            $userCache = array();

            // process the notifications for each output channel
            foreach ($user_by_handler as $handler => $users) {
                $handler = $this->handler[$handler];
                // and process each user for the current output channel
                foreach ($users as $userId) {
                    if (!$userCache[$userId]) {
                        $user = ilObjectFactory::getInstanceByObjId($userId, false);
                        if (!$user || !($user instanceof \ilObjUser)) {
                            continue;
                        }
                        $userCache[$userId] = $user;
                    }
                    $user = $userCache[$userId];

                    // optain the message instance for the user
                    // @todo this step could be cached on a per user basis
                    //	   as it is independed from the output handler
                    $instance = $notification->getUserInstance($user, $lang, $this->defaultLanguage);
                    foreach ($handler as $h) {
                        // fire the notification
                        $h->notify($instance);
                    }
                }
            }
        }
        // use async processing
        else {
            // just enque the current configuration
            ilNotificationDatabaseHandler::enqueueByUsers($notification, $users);
        }
    }

    /**
     * Sends the notification to all listener which are subscribed to the given
     * ref_id
     *
     * @param ilNotificationConfig $notification
     * @param type $ref_id
     * @param type $processAsync
     */
    private function toListeners(ilNotificationConfig $notification, $ref_id, $processAsync = false)
    {
        require_once 'Services/Notifications/classes/class.ilNotificationUserIterator.php';
        require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';

        if ($processAsync == false) {
            $users = ilNotificationDatabaseHandler::getUsersByListener($notification->getType(), $ref_id);
            self::toUsers($notification, $users, false);
            if ($notification->hasDisableAfterDeliverySet()) {
                ilNotificationDatabaseHandler::disableListeners($notification->getType(), $ref_id);
            }
        } else {
            ilNotificationDatabaseHandler::enqueueByListener($notification, $ref_id);
        }
    }

    /**
     * Send a notification to a list of roles. The recipients are fetched by calling
     * $rbacreview->assignedUsers($roles[$i]).
     *
     * @param ilNotificationConfig $notification
     * @param array $roles
     * @param boolean $processAsync
     */
    private function toRoles(ilNotificationConfig $notification, array $roles, $processAsync = false)
    {
        require_once 'Services/Notifications/classes/class.ilNotificationUserIterator.php';
        require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
        
        $users = array();
        foreach ($roles as $role) {
            $users[] = $this->rbacReview->assignedUsers($role);
        }
        // make sure to handle every user only once
        $users = array_unique(call_user_func_array('array_merge', $users));

        self::toUsers($notification, $users, $processAsync);
    }

    /**
     * @see ilNotificationSystem::toUsers()
     *
     * @param ilNotificationConfig $notification
     * @param int[] $users
     * @param boolean $processAsync
     */
    public static function sendNotificationToUsers(ilNotificationConfig $notification, $users, $processAsync = false)
    {
        self::getInstance()->toUsers($notification, $users, $processAsync);
    }

    /**
     * @see ilNotificationSystem::toListeners()
     *
     * @param ilNotificationConfig $notification
     * @param int $ref_id
     * @param boolean $processAsync
     */
    public static function sendNotificationToListeners(ilNotificationConfig $notification, $ref_id, $processAsync = false)
    {
        self::getInstance()->toListeners($notification, $ref_id, $processAsync);
    }
    
    /**
     * @see ilNotificationSystem::toRoles()
     *
     * @param ilNotificationConfig $notification
     * @param string[] $users
     * @param boolean $processAsync
     */
    public static function sendNotificationToRoles(ilNotificationConfig $notification, array $roles, $processAsync = false)
    {
        self::getInstance()->toRoles($notification, $roles, $processAsync);
    }

    public static function enableListeners($module, $ref_id)
    {
        require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
        ilNotificationDatabaseHandler::enableListeners($module, $ref_id);
    }

    public static function enableUserListeners($module, $ref_id, array $users)
    {
        if (!$users) {
            return;
        }
        require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
        ilNotificationDatabaseHandler::enableListeners($module, $ref_id, $users);
    }
}
