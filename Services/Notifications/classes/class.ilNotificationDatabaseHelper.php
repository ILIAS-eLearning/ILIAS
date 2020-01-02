<?php

require_once 'Services/Notifications/classes/class.ilNotificationSetupHelper.php';

class ilNotificationDatabaseHandler
{
    /**
     * @static
     * @param array $vars An array of placeholder types (title, longDescription or shortDescription, ...) and the corresponding ilNotificationParameter instance as their value
     * @return array
     */
    public static function getTranslatedLanguageVariablesOfNotificationParameters($vars = array())
    {
        global $DIC;

        $ilDB = $DIC->database();

        $where             = array();
        $langVarToTypeDict = array();

        foreach ($vars as $type => $var) {
            /**
             * @var $type string (title, longDescription or shortDescription, ...)
             * @var $var  ilNotificationParameter
             */
            if (!$var) {
                continue;
            }
            $where[]                            = sprintf('module = %s AND identifier = %s', $ilDB->quote($var->getLanguageModule()), $ilDB->quote($var->getName()));
            $langVarToTypeDict[$var->getName()] = $type;
        }

        if (!$where) {
            return array();
        }

        $query   = 'SELECT identifier, lang_key, value FROM lng_data WHERE (' . join(') OR (', $where) . ')';
        $res     = $ilDB->query($query);
        $results = array();

        while ($row = $ilDB->fetchAssoc($res)) {
            if (!$results[$row['identifier']]) {
                $results[$row['identifier']]                 = new stdClass();
                $results[$row['identifier']]->lang_untouched = array();
                $results[$row['identifier']]->params         = array();
            }
            $results[$row['identifier']]->lang_untouched[$row['lang_key']] = $row['value'];
        }

        return self::fillPlaceholders($results, $vars, $langVarToTypeDict);
    }

    /**
     * @static
     * @param array $results
     * @param array $vars
     * @param array $langVarToTypeDict
     * @return array mixed
     */
    protected static function fillPlaceholders($results, $vars, $langVarToTypeDict)
    {
        $pattern_old = '/##(.+?)##/im';
        $pattern     = '/\[(.+?)\]/im';

        foreach ($results as $langVar => $res) {
            $placeholdersStack = array();
            $res->lang         = array();

            foreach ($res->lang_untouched as $iso2shorthandle => $translation) {
                $translation = str_replace("\\n", "\n", $translation);
                $placeholdersStack[] = self::findPlaceholders($pattern, $translation);
                $translation = self::replaceFields($translation, $placeholdersStack[count($placeholdersStack) - 1], $vars[$langVarToTypeDict[$langVar]]->getParameters(), '[', ']');
                $placeholdersStack[] = self::findPlaceholders($pattern_old, $translation);
                $res->lang[$iso2shorthandle] = self::replaceFields($translation, $placeholdersStack[count($placeholdersStack) - 1], $vars[$langVarToTypeDict[$langVar]]->getParameters(), '##', '##');
            }

            $res->params = array_diff(
                array_unique(
                    call_user_func_array('array_merge', $placeholdersStack)
                ),
                array_keys($vars[$langVarToTypeDict[$langVar]]->getParameters())
            );
        }

        return $results;
    }

    /**
     * @static
     * @param string $pattern
     * @param string $translation
     * @return array
     */
    protected static function findPlaceholders($pattern, $translation)
    {
        $foundPlaceholders = array();
        preg_match_all($pattern, $translation, $foundPlaceholders);
        return (array) $foundPlaceholders[1];
    }

    /**
     * @static
     * @param string $string
     * @param array $foundPlaceholders
     * @param array $params
     * @param string $startTag
     * @param string $endTag
     * @return string
     */
    private static function replaceFields($string, $foundPlaceholders, $params, $startTag, $endTage)
    {
        foreach ($foundPlaceholders as $placeholder) {
            if (array_key_exists(strtoupper($placeholder), $params)) {
                $string = str_ireplace($startTag . $placeholder . $endTage, $params[strtoupper($placeholder)], $string);
            }
            if (array_key_exists(strtolower($placeholder), $params)) {
                $string = str_ireplace($startTag . $placeholder . $endTage, $params[strtolower($placeholder)], $string);
            }
        }
        return $string;
    }

    /**
     * Sets the configuration for all given configurations. Old configurations are
     * completly removed before the new are inserted.
     *
     * structure of $configArray
     *
     * array(
     *	 'chat_invitation' => array(
     *     'mail' => true,
     *	   'osd' => false
     *   ),
     *	 'adobeconnect_invitation' => array(
     *     'mail' => true,
     *	   'osd' => true
     *   ),
     * );
     *
     * If the userid is -1, the settings are stored as general settings (default
     * values or used if configuration type is set_by_admin).
     *
     * @global ilDB $ilDB
     * @param int $userid
     * @param array $configArray
     */
    public static function setUserConfig($userid, array $configArray)
    {
        global $DIC;

        $ilDB = $DIC->database();

        if ($userid != -1) {
            $channels = self::getAvailableChannels(array('set_by_user'));
            $types = self::getAvailableTypes(array('set_by_user'));
            $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE usr_id=%s AND ' . $ilDB->in('module', array_keys($types), false, 'text') . ' AND ' . $ilDB->in('channel', array_keys($channels), false, 'text');
        } else {
            $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE usr_id=%s';
        }

        $types = array('integer');
        $values = array($userid);
        
        // delete old settings
        $ilDB->manipulateF($query, $types, $values);

        foreach ($configArray as $type => $channels) {
            foreach ($channels as $channel => $value) {
                if (!$value) {
                    continue;
                }
                $ilDB->insert(
                    ilNotificationSetupHelper::$tbl_userconfig,
                    array(
                            'usr_id' => array('integer', $userid),
                            'module' => array('text', $type),
                            'channel' => array('text', $channel),
                        )
                );
            }
        }
    }

    public static function loadUserConfig($userid)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT module, channel FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE usr_id = %s';
        $types = array('integer');
        $values = array($userid);

        $res = $ilDB->queryF($query, $types, $values);

        $result = array();

        while ($row = $ilDB->fetchAssoc($res)) {
            if (!$result[$row['module']]) {
                $result[$row['module']] = array();
            }

            $result[$row['module']][] = $row['channel'];
        }

        return $result;
    }

    public static function enqueueByUsers(ilNotificationConfig $notification, array $userids)
    {
        if (!$userids) {
            return;
        }

        global $DIC;

        $ilDB = $DIC->database();

        $notification_id = ilNotificationDatabaseHandler::storeNotification($notification);
        $valid_until     = $notification->getValidForSeconds() ? (time() + $notification->getValidForSeconds()) : 0;

        foreach ($userids as $userid) {
            $ilDB->insert(
                ilNotificationSetupHelper::$tbl_notification_queue,
                array(
                    'notification_id' => array('integer', $notification_id),
                    'usr_id'          => array('integer', $userid),
                    'valid_until'     => array('integer', $valid_until),
                    'visible_for'     => array('integer', $notification->getVisibleForSeconds())
                )
            );
        }
    }

    public static function enqueueByListener(ilNotificationConfig $notification, $ref_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $notification_id = ilNotificationDatabaseHandler::storeNotification($notification);
        $valid_until = $notification->getValidForSeconds() ? (time() + $notification->getValidForSeconds()) : 0;

        $query = 'INSERT INTO ' . ilNotificationSetupHelper::$tbl_notification_queue . ' (notification_id, usr_id, valid_until, visible_for) '
                . ' (SELECT %s, usr_id, %s, %s FROM ' . ilNotificationSetupHelper::$tbl_userlistener . ' WHERE disabled = 0 AND module = %s AND sender_id = %s)';

        $types = array('integer', 'integer', 'integer', 'text', 'integer');

        $values = array($notification_id, $valid_until, $notification->getVisibleForSeconds(), $notification->getType(), $ref_id);

        $ilDB->manipulateF($query, $types, $values);
    }

    public static function storeNotification(ilNotificationConfig $notification)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $id = $ilDB->nextId(ilNotificationSetupHelper::$tbl_notification_data);

        $ilDB->insert(
            ilNotificationSetupHelper::$tbl_notification_data,
            array(
                    'notification_id' => array('integer', $id),
                    'serialized' => array('text', serialize($notification)),
                )
        );

        return $id;
    }

    public static function removeNotification($id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_notification_data . ' WHERE notification_id = ?';
        $types = array('integer');
        $values = array($id);

        $ilDB->manipulateF($query, $types, $values);
    }

    public static function getUsersByListener($module, $sender_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT usr_id FROM ' . ilNotificationSetupHelper::$tbl_userlistener . ' WHERE disabled = 0 AND module = %s AND sender_id = %s';
        $types = array('text', 'integer');
        $values = array($module, $sender_id);

        $users = array();

        $rset = $ilDB->queryF($query, $types, $values);
        while ($row = $ilDB->fetchAssoc($rset)) {
            $users[] = $row['usr_id'];
        }
        return $users;
    }

    public static function disableListeners($module, $sender_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'UPDATE ' . ilNotificationSetupHelper::$tbl_userlistener . ' SET disabled = 1 WHERE module = %s AND sender_id = %s';
        $types = array('text', 'integer');
        $values = array($module, $sender_id);

        $ilDB->manipulateF($query, $types, $values);
    }
    
    public static function enableListeners($module, $sender_id, array $users = array())
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'UPDATE ' . ilNotificationSetupHelper::$tbl_userlistener . ' SET disabled = 0 WHERE module = %s AND sender_id = %s';

        if ($users) {
            $query .= ' ' . $ilDB->in('usr_id', $users);
        }

        $types = array('text', 'integer');
        $values = array($module, $sender_id);

        $ilDB->manipulateF($query, $types, $values);
    }

    /**
     * Registers a new notification channel for distributing notifications
     *
     * @global ilDB $ilDB
     *
     * @param type $name        technical name of the type
     * @param type $title       human readable title for configuration guis
     * @param type $description not yet used human readable description
     * @param type $class       class name of the handler class
     * @param type $classfile   class file location of the handler class
     * @param type $config_type 'set_by_user' or 'set_by_admin'; restricts if users can override the configuartion for this channel
     */
    public static function registerChannel($name, $title, $description, $class, $classfile, $config_type)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->insert(
            ilNotificationSetupHelper::$tbl_notification_channels,
            array(
                    'channel_name' => array('text', $name),
                    'title' => array('text', $title),
                    'description' => array('text', $description),
                    'class' => array('text', $class),
                    'include' => array('text', $classfile),
                    'config_type' => array('text', $config_type),
                )
        );
    }

    /**
     * Registers a new notification type.
     *
     * @global ilDB $ilDB
     *
     * @param string $name               technical name of the type
     * @param string $title              human readable title for configuration guis
     * @param string $description        not yet used human readable description
     * @param string $notification_group not yet used group
     * @param string $config_type        'set_by_user' or 'set_by_admin'; restricts if users can override the configuartion for this type
     */
    public static function registerType($name, $title, $description, $notification_group, $config_type)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->insert(
            ilNotificationSetupHelper::$tbl_notification_types,
            array(
                    'type_name' => array('text', $name),
                    'title' => array('text', $title),
                    'description' => array('text', $description),
                    'notification_group' => array('text', $notification_group),
                    'config_type' => array('text', $config_type),
                )
        );
    }

    public static function getAvailableChannels($config_types = array(), $includeDisabled = false)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT channel_name, title, description, class, include, config_type FROM ' . ilNotificationSetupHelper::$tbl_notification_channels;
        if ($config_types) {
            $query .= ' WHERE ' . $ilDB->in('config_type', $config_types, false, 'text');
        }

        $rset = $ilDB->query($query);

        $result = array();

        $settings = new ilSetting('notifications');

        while ($row = $ilDB->fetchAssoc($rset)) {
            if (!$includeDisabled && !$settings->get('enable_' . $row['channel_name'])) {
                continue;
            }
            
            $result[$row['channel_name']] = array(
                'name' => $row['channel_name'],
                'title' => $row['title'],
                'description' => $row['description'],
                'handler' => $row['class'],
                'include' => $row['include'],
                'config_type' => $row['config_type'],
            );
        }

        return $result;
    }

    public static function getAvailableTypes($config_types = array())
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT type_name, title, description, notification_group, config_type FROM ' . ilNotificationSetupHelper::$tbl_notification_types;
        if ($config_types) {
            $query .= ' WHERE ' . $ilDB->in('config_type', $config_types, false, 'text');
        }


        $rset = $ilDB->query($query);

        $result = array();

        while ($row = $ilDB->fetchAssoc($rset)) {
            $result[$row['type_name']] = array(
                'name' => $row['type_name'],
                'title' => $row['title'],
                'description' => $row['description'],
                'group' => $row['notification_group'],
                'config_type' => $row['config_type'],
            );
        }

        return $result;
    }

    public static function setConfigTypeForType($type_name, $config_name)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'UPDATE ' . ilNotificationSetupHelper::$tbl_notification_types . ' SET config_type = %s WHERE type_name = %s';
        $types = array('text', 'text');
        $values = array($config_name, $type_name);
        $ilDB->manipulateF($query, $types, $values);
    }

    public static function setConfigTypeForChannel($channel_name, $config_name)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'UPDATE ' . ilNotificationSetupHelper::$tbl_notification_channels . ' SET config_type = %s WHERE channel_name = %s';
        $types = array('text', 'text');
        $values = array($config_name, $channel_name);
        $ilDB->manipulateF($query, $types, $values);
    }


    public static function getUsersWithCustomConfig(array $userid)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT usr_id, value FROM usr_pref WHERE ' . $ilDB->in('usr_id', $userid, false, 'integer') . ' AND keyword="use_custom_notification_setting" AND value="1"';
        $rset = $ilDB->query($query);
        $result = array();
        while ($row = $ilDB->fetchAssoc($rset)) {
            $result[$row['usr_id']] = (bool) $row['value'];
        }
        return $result;
    }
}
