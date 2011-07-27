<?php

require_once 'Services/Notifications/classes/class.ilNotificationSetupHelper.php';

class ilNotificationDatabaseHandler {

    public static function getLanguageVars($vars = array()) {
        global $ilDB;

        $varToTypeDict = array();

        foreach ($vars as $type => $var) {
            if (!$var) {
                continue;
            }
            $where[] = sprintf('module=%s AND identifier=%s', $ilDB->quote($var->getLanguageModule()), $ilDB->quote($var->getName()));

            $varToTypeDict[$var->getName()] = $type;
        }

        if (!$where) {
            return array();
        }

        $query = 'SELECT identifier, lang_key, value FROM lng_data WHERE (' . join(') OR (', $where) . ')';
        $res = $ilDB->query($query);
        $results = array();

        while ($row = $ilDB->fetchAssoc($res)) {
            if (!$results[$row['identifier']]) {
                $results[$row['identifier']] = new stdClass();
                $results[$row['identifier']]->lang_untouched = array();
                $results[$row['identifier']]->params = array();
            }
            $results[$row['identifier']]->lang_untouched[$row['lang_key']] = $row['value'];
        }

        $pattern = '/##(.*?)##/im';
        foreach ($results as $key => $res) {
            $keyVars = array();
            $res->lang = array();

            #var_dump($vars, $varToTypeDict[$key]);

            foreach ($res->lang_untouched as $lng => $value) {
		$value = str_replace("\\n", "\n", $value);
                preg_match_all($pattern, $value, $matches);
                $keyVars[] = $matches[1];

                $res->lang[$lng] = self::replaceFields($value, $matches[1], $vars[$varToTypeDict[$key]]->getParameters());
            }

            $res->params = array_diff(
                            array_unique(
                                    call_user_func_array('array_merge', $keyVars)
                            ),
                            array_keys($vars[$varToTypeDict[$key]]->getParameters())
            );
        }

        return $results;
    }

    private static function replaceFields($string, $keys, $params) {
        foreach ($keys as $key) {
            if (array_key_exists($key, $params)) {
                $string = str_replace('##' . $key . '##', $params[$key], $string);
            }
        }
        return $string;
    }

    public static function setUserConfig($userid, array $configArray) {
        global $ilDB;

        if ($userid != -1) {
            $channels = self::getAvailableChannels(array('set_by_user'));
            $types = self::getAvailableTypes(array('set_by_user'));
            $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE usr_id=%s AND ' . $ilDB->in('module', array_keys($types), false, 'text') . ' AND ' . $ilDB->in('channel', array_keys($channels), false, 'text');
        }
        else {
            $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE usr_id=%s';
        }

        $types = array('integer');
        $values = array($userid);
        $ilDB->manipulateF($query, $types, $values);

        foreach ($configArray as $type => $channels) {
            foreach ($channels as $channel => $value) {
                if (!$value)
                    continue;
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

    public static function loadUserConfig($userid) {
        global $ilDB;

        $query = 'SELECT module, channel FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE usr_id = %s';
        $types = array('integer');
        $values = array($userid);

        $res = $ilDB->queryF($query, $types, $values);

        $result = array();

        while ($row = $ilDB->fetchAssoc($res)) {
            if (!$result[$row['module']])
                $result[$row['module']] = array();

            $result[$row['module']][] = $row['channel'];
        }

        return $result;
    }

    public static function enqueueByUsers(ilNotificationConfig $notification, array $userids) {
        if (!$userids)
            return;

        global $ilDB;

        $notification_id = ilNotificationDatabaseHandler::storeNotification($notification);
        $valid_until = $notification->getValidForSeconds() ? (time() + $notification->getValidForSeconds()) : 0;

        foreach($userids as $userid) {
            $ilDB->insert(
                ilNotificationSetupHelper::$tbl_notification_queue,
                    array(
                        'notification_id' => array('integer', $notification_id),
                        'usr_id' => array('integer', $userid),
                        'valid_until' => array('integer', $valid_until),
                    )
            );
        }

    }

    public static function enqueueByListener(ilNotificationConfig $notification, $ref_id) {
        global $ilDB;

        $notification_id = ilNotificationDatabaseHandler::storeNotification($notification);
        $valid_until = $notification->getValidForSeconds() ? (time() + $notification->getValidForSeconds()) : 0;

        $query = 'INSERT INTO ' . ilNotificationSetupHelper::$tbl_notification_queue . ' (notification_id, usr_id, valid_until) '
                .' (SELECT %s, usr_id, %s FROM '. ilNotificationSetupHelper::$tbl_userlistener .' WHERE disabled = 0 AND module = %s AND sender_id = %s)';

        $types = array('integer', 'integer', 'text', 'integer');

        $values = array($notification_id, $valid_until, $notification->getType(), $ref_id);

        $ilDB->manipulateF($query, $types, $values);
    }
/*
    public static function enqueueByRoles(ilNotificationConfig $notification, array $roles) {
        if (!$roles)
            return;

        global $ilDB;

        $notification_id = ilNotificationDatabaseHandler::storeNotification($notification);
        $valid_until = $notification->getValidForSeconds() ? (time() + $notification->getValidForSeconds()) : 0;

        foreach($userids as $userid) {
            $ilDB->insert(
                ilNotificationSetupHelper::$tbl_notification_queue,
                    array(
                        'notification_id' => array('integer', $notification_id),
                        'usr_id' => array('integer', $userid),
                        'valid_until' => array('integer', $valid_until),
                    )
            );
        }

    }
*/
    public static function storeNotification(ilNotificationConfig $notification) {
        global $ilDB;

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

    public static function removeNotification($id) {
        global $ilDB;

        $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_notification_data . ' WHERE notification_id = ?';
        $types = array('integer');
        $values = array($id);

        $ilDB->manipulateF($query, $types, $values);
    }

    public static function getUsersByListener($module, $sender_id) {
        global $ilDB;

        $query = 'SELECT usr_id FROM '. ilNotificationSetupHelper::$tbl_userlistener .' WHERE disabled = 0 AND module = %s AND sender_id = %s';
        $types = array('text', 'integer');
        $values = array($module, $sender_id);

        $users = array();

        $rset = $ilDB->queryF($query, $types, $values);
        while($row = $ilDB->fetchAssoc($rset)) {
            $users[] = $row['usr_id'];
        }
        return $users;
    }

    public static function disableListeners($module, $sender_id) {
        global $ilDB;

        $query = 'UPDATE '. ilNotificationSetupHelper::$tbl_userlistener .' SET disabled = 1 WHERE module = %s AND sender_id = %s';
        $types = array('text', 'integer');
        $values = array($module, $sender_id);

        $ilDB->manipulateF($query, $types, $values);
    }
    
    public static function enableListeners($module, $sender_id, array $users = array()) {
        global $ilDB;

        $query = 'UPDATE '. ilNotificationSetupHelper::$tbl_userlistener .' SET disabled = 0 WHERE module = %s AND sender_id = %s';

        if ($users) {
            $query .= ' ' . $ilDB->in('usr_id', $users);
        }

        $types = array('text', 'integer');
        $values = array($module, $sender_id);

        $ilDB->manipulateF($query, $types, $values);
    }

    public static function registerChannel($name, $title, $description, $class, $classfile, $config_type) {
        global $ilDB;
        
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

    public static function registerType($name, $title, $description, $notification_group, $config_type) {
        global $ilDB;

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

    public static function getAvailableChannels($config_types = array(), $includeDisabled = false) {
        global $ilDB;

        $query = 'SELECT channel_name, title, description, class, include, config_type FROM ' . ilNotificationSetupHelper::$tbl_notification_channels;
        if ($config_types)
            $query .= ' WHERE ' . $ilDB->in('config_type', $config_types, false, 'text');

        $rset = $ilDB->query($query);

        $result = array();

        $settings = new ilSetting('notifications');

        while ($row = $ilDB->fetchAssoc($rset)) {
            if (!$includeDisabled && !$settings->get('enable_' . $row['channel_name']))
                    continue;
            
            $result[$row['channel_name']] = array (
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

    public static function getAvailableTypes($config_types = array()) {
        global $ilDB;

        $query = 'SELECT type_name, title, description, notification_group, config_type FROM ' . ilNotificationSetupHelper::$tbl_notification_types;
        if ($config_types)
            $query .= ' WHERE ' . $ilDB->in('config_type', $config_types, false, 'text');


        $rset = $ilDB->query($query);

        $result = array();

        while ($row = $ilDB->fetchAssoc($rset)) {
            $result[$row['type_name']] = array (
                'name' => $row['type_name'],
                'title' => $row['title'],
                'description' => $row['description'],
                'group' => $row['notification_group'],
                'config_type' => $row['config_type'],
            );
        }

        return $result;
    }

    public static function setConfigTypeForType($type_name, $config_name) {
        global $ilDB;
        $query = 'UPDATE ' . ilNotificationSetupHelper::$tbl_notification_types . ' SET config_type = %s WHERE type_name = %s';
        $types = array('text', 'text');
        $values = array($config_name, $type_name);
        $ilDB->manipulateF($query, $types, $values);
    }

    public static function setConfigTypeForChannel($channel_name, $config_name) {
        global $ilDB;
        $query = 'UPDATE ' . ilNotificationSetupHelper::$tbl_notification_channels . ' SET config_type = %s WHERE channel_name = %s';
        $types = array('text', 'text');
        $values = array($config_name, $channel_name);
        $ilDB->manipulateF($query, $types, $values);
    }


    public static function getUsersWithCustomConfig(array $userid) {
        global $ilDB;
        $query = 'SELECT usr_id, value FROM usr_pref WHERE ' . $ilDB->in('usr_id', $userid, false, 'integer') . ' AND keyword="use_custom_notification_setting" AND value="1"';
        $rset = $ilDB->query($query);
        $result = array();
        while($row = $ilDB->fetchAssoc($rset)) {
            $result[$row['usr_id']] = (bool)$row['value'];
        }
        return $result;
    }
}
