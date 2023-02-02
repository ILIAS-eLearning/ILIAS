<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\Notifications;

use ilDBInterface;
use ILIAS\Notifications\Model\ilNotificationConfig;
use ILIAS\Notifications\Model\ilNotificationParameter;
use ilSetting;
use stdClass;

/**
 * @author Jan Posselt <jposselt@databay.de>
 */
class ilNotificationDatabaseHandler
{
    /**
     * @param array<string, ilNotificationParameter> $vars
     */
    public static function getTranslatedLanguageVariablesOfNotificationParameters(array $vars = []): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $where = [];
        $langVarToTypeDict = [];

        foreach ($vars as $type => $var) {
            if (!$var) {
                continue;
            }
            $where[] = sprintf('module = %s AND identifier = %s', $ilDB->quote($var->getLanguageModule()), $ilDB->quote($var->getName()));
            $langVarToTypeDict[$var->getName()] = $type;
        }

        if (!$where) {
            return [];
        }

        $query = 'SELECT identifier, lang_key, value FROM lng_data WHERE (' . implode(') OR (', $where) . ')';
        $res = $ilDB->query($query);
        $results = [];

        while ($row = $ilDB->fetchAssoc($res)) {
            if (!isset($results[$row['identifier']]) || !$results[$row['identifier']]) {
                $results[$row['identifier']] = new stdClass();
                $results[$row['identifier']]->lang_untouched = [];
                $results[$row['identifier']]->params = [];
            }
            $results[$row['identifier']]->lang_untouched[$row['lang_key']] = $row['value'];
        }

        return self::fillPlaceholders($results, $vars, $langVarToTypeDict);
    }

    /**
     * @param array<string, ilNotificationParameter> $vars
     */
    protected static function fillPlaceholders(array $results, array $vars, array $langVarToTypeDict): array
    {
        $pattern_old = '/##(.+?)##/im';
        $pattern = '/\[(.+?)\]/im';

        foreach ($results as $langVar => $res) {
            $placeholdersStack = [];
            $res->lang = [];

            foreach ($res->lang_untouched as $iso2shorthandle => $translation) {
                $translation = str_replace("\\n", "\n", $translation);
                $placeholdersStack[] = self::findPlaceholders($pattern, $translation);
                $translation = self::replaceFields($translation, $placeholdersStack[count($placeholdersStack) - 1], $vars[$langVarToTypeDict[$langVar]]->getParameters(), '[', ']');
                $placeholdersStack[] = self::findPlaceholders($pattern_old, $translation);
                $res->lang[$iso2shorthandle] = self::replaceFields($translation, $placeholdersStack[count($placeholdersStack) - 1], $vars[$langVarToTypeDict[$langVar]]->getParameters(), '##', '##');
            }

            $res->params = array_diff(
                array_unique(
                    array_merge(...$placeholdersStack)
                ),
                array_keys($vars[$langVarToTypeDict[$langVar]]->getParameters())
            );
        }

        return $results;
    }

    protected static function findPlaceholders(string $pattern, string $translation): array
    {
        $foundPlaceholders = [];
        preg_match_all($pattern, $translation, $foundPlaceholders);
        return (array) $foundPlaceholders[1];
    }

    /**
     * @param string[] $foundPlaceholders
     * @param string[] $params
     */
    private static function replaceFields(string $string, array $foundPlaceholders, array $params, string $startTag, string $endTage): string
    {
        $result = $string;
        foreach ($foundPlaceholders as $placeholder) {
            if (array_key_exists(strtoupper($placeholder), $params)) {
                $result = str_ireplace($startTag . $placeholder . $endTage, $params[strtoupper($placeholder)], $result);
            }
            if (array_key_exists(strtolower($placeholder), $params)) {
                $result = str_ireplace($startTag . $placeholder . $endTage, $params[strtolower($placeholder)], $result);
            }
        }
        return $result;
    }

    public static function setUserConfig(int $userid, array $configArray): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        if ($userid !== -1) {
            $channels = self::getAvailableChannels(['set_by_user']);
            $types = self::getAvailableTypes(['set_by_user']);
            $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE usr_id=%s AND ' . $ilDB->in('module', array_keys($types), false, 'text') . ' AND ' . $ilDB->in('channel', array_keys($channels), false, 'text');
        } else {
            $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE usr_id=%s';
        }

        $types = ['integer'];
        $values = [$userid];

        // delete old settings
        $ilDB->manipulateF($query, $types, $values);

        foreach ($configArray as $type => $channels) {
            foreach ($channels as $channel => $value) {
                if (!$value) {
                    continue;
                }
                $ilDB->insert(
                    ilNotificationSetupHelper::$tbl_userconfig,
                    [
                        'usr_id' => ['integer', $userid],
                        'module' => ['text', $type],
                        'channel' => ['text', $channel],
                    ]
                );
            }
        }
    }

    /**
     * @param int $userid
     *
     * @return string[][]
     */
    public static function loadUserConfig(int $userid): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT module, channel FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE usr_id = %s';
        $types = ['integer'];
        $values = [$userid];

        $res = $ilDB->queryF($query, $types, $values);

        $result = [];

        while ($row = $ilDB->fetchAssoc($res)) {
            if (!isset($result[$row['module']])) {
                $result[$row['module']] = [];
            }

            $result[$row['module']][] = $row['channel'];
        }

        return $result;
    }

    public static function enqueueByUsers(ilNotificationConfig $notification, array $userids): void
    {
        if (!$userids) {
            return;
        }

        global $DIC;

        $ilDB = $DIC->database();

        $notification_id = self::storeNotification($notification);
        $valid_until = $notification->getValidForSeconds() ? (time() + $notification->getValidForSeconds()) : 0;

        foreach ($userids as $userid) {
            $ilDB->insert(
                ilNotificationSetupHelper::$tbl_notification_queue,
                [
                    'notification_id' => ['integer', $notification_id],
                    'usr_id' => ['integer', $userid],
                    'valid_until' => ['integer', $valid_until],
                    'visible_for' => ['integer', $notification->getVisibleForSeconds()]
                ]
            );
        }
    }

    public static function enqueueByListener(ilNotificationConfig $notification, int $ref_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $notification_id = self::storeNotification($notification);
        $valid_until = $notification->getValidForSeconds() ? (time() + $notification->getValidForSeconds()) : 0;

        $query = 'INSERT INTO ' . ilNotificationSetupHelper::$tbl_notification_queue . ' (notification_id, usr_id, valid_until, visible_for) '
                . ' (SELECT %s, usr_id, %s, %s FROM ' . ilNotificationSetupHelper::$tbl_userlistener . ' WHERE disabled = 0 AND module = %s AND sender_id = %s)';

        $types = ['integer', 'integer', 'integer', 'text', 'integer'];

        $values = [$notification_id, $valid_until, $notification->getVisibleForSeconds(), $notification->getType(), $ref_id];

        $ilDB->manipulateF($query, $types, $values);
    }

    public static function storeNotification(ilNotificationConfig $notification): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $id = $ilDB->nextId(ilNotificationSetupHelper::$tbl_notification_data);

        $ilDB->insert(
            ilNotificationSetupHelper::$tbl_notification_data,
            [
                'notification_id' => ['integer', $id],
                'serialized' => ['text', serialize($notification)],
            ]
        );

        return $id;
    }

    public static function removeNotification(int $id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_notification_data . ' WHERE notification_id = ?';
        $types = ['integer'];
        $values = [$id];

        $ilDB->manipulateF($query, $types, $values);
    }

    /**
     * @return int[]
     */
    public static function getUsersByListener(string $module, int $sender_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT usr_id FROM ' . ilNotificationSetupHelper::$tbl_userlistener . ' WHERE disabled = 0 AND module = %s AND sender_id = %s';
        $types = ['text', 'integer'];
        $values = [$module, $sender_id];

        $users = [];

        $rset = $ilDB->queryF($query, $types, $values);
        while ($row = $ilDB->fetchAssoc($rset)) {
            $users[] = (int) $row['usr_id'];
        }
        return $users;
    }

    public static function disableListeners(string $module, int $sender_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'UPDATE ' . ilNotificationSetupHelper::$tbl_userlistener . ' SET disabled = 1 WHERE module = %s AND sender_id = %s';
        $types = ['text', 'integer'];
        $values = [$module, $sender_id];

        $ilDB->manipulateF($query, $types, $values);
    }

    public static function enableListeners(string $module, $sender_id, array $users = []): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'UPDATE ' . ilNotificationSetupHelper::$tbl_userlistener . ' SET disabled = 0 WHERE module = %s AND sender_id = %s';

        if ($users) {
            $query .= ' ' . $ilDB->in('usr_id', $users);
        }

        $types = ['text', 'integer'];
        $values = [$module, $sender_id];

        $ilDB->manipulateF($query, $types, $values);
    }

    public static function registerChannel(ilDBInterface $db, string $name, string $title, string $description, string $class, string $classfile, string $config_type): void
    {
        $db->insert(
            ilNotificationSetupHelper::$tbl_notification_channels,
            [
                'channel_name' => ['text', $name],
                'title' => ['text', $title],
                'description' => ['text', $description],
                'class' => ['text', $class],
                'include' => ['text', $classfile],
                'config_type' => ['text', $config_type],
            ]
        );
    }

    public static function registerType(ilDBInterface $db, string $name, string $title, string $description, string $notification_group, string $config_type): void
    {
        $db->insert(
            ilNotificationSetupHelper::$tbl_notification_types,
            [
                'type_name' => ['text', $name],
                'title' => ['text', $title],
                'description' => ['text', $description],
                'notification_group' => ['text', $notification_group],
                'config_type' => ['text', $config_type],
            ]
        );
    }

    /**
     * @return string[][]
     */
    public static function getAvailableChannels(array $config_types = [], bool $includeDisabled = false): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT channel_name, title, description, class, include, config_type FROM ' . ilNotificationSetupHelper::$tbl_notification_channels;
        if ($config_types) {
            $query .= ' WHERE ' . $ilDB->in('config_type', $config_types, false, 'text');
        }

        $rset = $ilDB->query($query);

        $result = [];

        $settings = new ilSetting('notifications');

        while ($row = $ilDB->fetchAssoc($rset)) {
            if (!$includeDisabled && !$settings->get('enable_' . $row['channel_name'])) {
                continue;
            }

            $result[$row['channel_name']] = [
                'name' => $row['channel_name'],
                'title' => $row['title'],
                'description' => $row['description'],
                'handler' => $row['class'],
                'include' => $row['include'],
                'config_type' => $row['config_type'],
            ];
        }

        return $result;
    }

    /**
     * @return string[][]
     */
    public static function getAvailableTypes(array $config_types = []): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT type_name, title, description, notification_group, config_type FROM ' . ilNotificationSetupHelper::$tbl_notification_types;
        if ($config_types) {
            $query .= ' WHERE ' . $ilDB->in('config_type', $config_types, false, 'text');
        }


        $rset = $ilDB->query($query);

        $result = [];

        while ($row = $ilDB->fetchAssoc($rset)) {
            $result[$row['type_name']] = [
                'name' => $row['type_name'],
                'title' => $row['title'],
                'description' => $row['description'],
                'group' => $row['notification_group'],
                'config_type' => $row['config_type'],
            ];
        }

        return $result;
    }

    public static function setConfigTypeForType(string $type_name, string $config_name): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'UPDATE ' . ilNotificationSetupHelper::$tbl_notification_types . ' SET config_type = %s WHERE type_name = %s';
        $types = ['text', 'text'];
        $values = [$config_name, $type_name];
        $ilDB->manipulateF($query, $types, $values);
    }

    public static function setConfigTypeForChannel(string $channel_name, string $config_name): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'UPDATE ' . ilNotificationSetupHelper::$tbl_notification_channels . ' SET config_type = %s WHERE channel_name = %s';
        $types = ['text', 'text'];
        $values = [$config_name, $channel_name];
        $ilDB->manipulateF($query, $types, $values);
    }

    /**
     * @param int[] $userid
     *
     * @return bool[]
     */
    public static function getUsersWithCustomConfig(array $userid): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT usr_id, value FROM usr_pref WHERE ' . $ilDB->in('usr_id', $userid, false, 'integer') . ' AND keyword="use_custom_notification_setting" AND value="1"';
        $rset = $ilDB->query($query);
        $result = [];
        while ($row = $ilDB->fetchAssoc($rset)) {
            $result[$row['usr_id']] = (bool) $row['value'];
        }
        return $result;
    }
}
