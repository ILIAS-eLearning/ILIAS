<?php

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

declare(strict_types=1);

namespace ILIAS\Notifications;

use ilDBInterface;
use ILIAS\Notifications\Model\ilNotificationConfig;
use ILIAS\Notifications\Model\ilNotificationParameter;
use ilSetting;
use stdClass;
use ilDBConstants;

/**
 * @author Jan Posselt <jposselt@databay.de>
 */
class ilNotificationDatabaseHandler
{
    /**
     * @param array<string, ilNotificationParameter> $vars
     * @return array<string, object{lang_untouched: array<string, string>, lang: array<string, string>, params: list<string>}>
     */
    public static function getTranslatedLanguageVariablesOfNotificationParameters(array $vars = []): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $where = [];
        $lang_var_to_type_dict = [];

        foreach ($vars as $type => $var) {
            $where[] = sprintf(
                'module = %s AND identifier = %s',
                $ilDB->quote($var->getLanguageModule()),
                $ilDB->quote($var->getName())
            );

            $lang_var_to_type_dict[$var->getName()] = $type;
        }

        if (!$where) {
            return [];
        }

        $query = 'SELECT identifier, lang_key, value FROM lng_data WHERE (' . implode(') OR (', $where) . ')';
        $res = $ilDB->query($query);
        $results = [];

        while ($row = $ilDB->fetchAssoc($res)) {
            if (!isset($results[$row['identifier']])) {
                $results[$row['identifier']] = new stdClass();
                $results[$row['identifier']]->lang_untouched = [];
                $results[$row['identifier']]->params = [];
            }
            $results[$row['identifier']]->lang_untouched[$row['lang_key']] = $row['value'];
        }

        return self::fillPlaceholders($results, $vars, $lang_var_to_type_dict);
    }

    /**
     * @param array<string, object{lang_untouched: array<string, string>, params: list<string>}> $results
     * @param array<string, ilNotificationParameter>                                             $vars
     * @param array<string, string>                                                              $lang_var_to_type_dict
     * @return array<string, object{lang_untouched: array<string, string>, lang: array<string, string>, params: list<string>}>
     */
    protected static function fillPlaceholders(array $results, array $vars, array $lang_var_to_type_dict): array
    {
        $pattern_old = '/##(.+?)##/im';
        $pattern = '/\[(.+?)\]/im';

        foreach ($results as $lang_var => $res) {
            $placeholders_stack = [];
            $res->lang = [];

            foreach ($res->lang_untouched as $iso2_short_handle => $translation) {
                $translation = str_replace("\\n", "\n", (string) $translation);
                $placeholders_stack[] = self::findPlaceholders($pattern, $translation);
                $translation = self::replaceFields(
                    $translation,
                    $placeholders_stack[count($placeholders_stack) - 1],
                    $vars[$lang_var_to_type_dict[$lang_var]]->getParameters(),
                    '[',
                    ']'
                );
                $placeholders_stack[] = self::findPlaceholders($pattern_old, $translation);
                $res->lang[$iso2_short_handle] = self::replaceFields(
                    $translation,
                    $placeholders_stack[count($placeholders_stack) - 1],
                    $vars[$lang_var_to_type_dict[$lang_var]]->getParameters(),
                    '##',
                    '##'
                );
            }

            $res->params = array_diff(
                array_unique(
                    array_merge(...$placeholders_stack)
                ),
                array_keys($vars[$lang_var_to_type_dict[$lang_var]]->getParameters())
            );
        }

        return $results;
    }

    /**
     * @return list<string>
     */
    protected static function findPlaceholders(string $pattern, string $translation): array
    {
        $foundPlaceholders = [];
        preg_match_all($pattern, $translation, $foundPlaceholders);

        return (array) $foundPlaceholders[1];
    }

    /**
     * @param list<string> $foundPlaceholders
     * @param array<string, mixed> $params
     */
    private static function replaceFields(
        string $string,
        array $foundPlaceholders,
        array $params,
        string $startTag,
        string $endTage
    ): string {
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

    /**
     * @param array<string, array<string, string>> $configArray
     */
    public static function setUserConfig(int $userid, array $configArray): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        if ($userid !== -1) {
            $channels = self::getAvailableChannels(['set_by_user']);
            $types = self::getAvailableTypes(['set_by_user']);
            $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE usr_id = %s AND ' . $ilDB->in(
                'module',
                array_keys($types),
                false,
                ilDBConstants::T_TEXT
            ) . ' AND ' . $ilDB->in('channel', array_keys($channels), false, ilDBConstants::T_TEXT);
        } else {
            $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE usr_id = %s';
        }

        $types = [ilDBConstants::T_INTEGER];
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
                        'usr_id' => [ilDBConstants::T_INTEGER, $userid],
                        'module' => [ilDBConstants::T_TEXT, $type],
                        'channel' => [ilDBConstants::T_TEXT, $channel],
                    ]
                );
            }
        }
    }

    /**
     * @return array<string, list<string>>
     */
    public static function loadUserConfig(int $userid): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT module, channel FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE usr_id = %s';
        $types = [ilDBConstants::T_INTEGER];
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

    /**
     * @param list<int> $usr_ids
     */
    public static function enqueueByUsers(ilNotificationConfig $notification, array $usr_ids): void
    {
        if (!$usr_ids) {
            return;
        }

        global $DIC;

        $ilDB = $DIC->database();

        $notification_id = self::storeNotification($notification);
        $valid_until = $notification->getValidForSeconds() ? (time() + $notification->getValidForSeconds()) : 0;

        foreach ($usr_ids as $userid) {
            $ilDB->insert(
                ilNotificationSetupHelper::$tbl_notification_queue,
                [
                    'notification_id' => [ilDBConstants::T_INTEGER, $notification_id],
                    'usr_id' => [ilDBConstants::T_INTEGER, $userid],
                    'valid_until' => [ilDBConstants::T_INTEGER, $valid_until],
                    'visible_for' => [ilDBConstants::T_INTEGER, $notification->getVisibleForSeconds()]
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

        $types = [
            ilDBConstants::T_INTEGER,
            ilDBConstants::T_INTEGER,
            ilDBConstants::T_INTEGER,
            ilDBConstants::T_TEXT,
            ilDBConstants::T_INTEGER
        ];

        $values = [
            $notification_id,
            $valid_until,
            $notification->getVisibleForSeconds(),
            $notification->getType(),
            $ref_id
        ];

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
                'notification_id' => [ilDBConstants::T_INTEGER, $id],
                'serialized' => [ilDBConstants::T_TEXT, serialize($notification)],
            ]
        );

        return $id;
    }

    public static function removeNotification(int $id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'DELETE FROM ' . ilNotificationSetupHelper::$tbl_notification_data . ' WHERE notification_id = %s';
        $types = [ilDBConstants::T_INTEGER];
        $values = [$id];

        $ilDB->manipulateF($query, $types, $values);
    }

    /**
     * @return list<int>
     */
    public static function getUsersByListener(string $module, int $sender_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT usr_id FROM ' . ilNotificationSetupHelper::$tbl_userlistener . ' WHERE disabled = 0 AND module = %s AND sender_id = %s';
        $types = [ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER];
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
        $types = [ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER];
        $values = [$module, $sender_id];

        $ilDB->manipulateF($query, $types, $values);
    }

    /**
     * @param list<int> $users
     */
    public static function enableListeners(string $module, int $sender_id, array $users = []): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'UPDATE ' . ilNotificationSetupHelper::$tbl_userlistener . ' SET disabled = 0 WHERE module = %s AND sender_id = %s';

        if ($users) {
            $query .= ' ' . $ilDB->in('usr_id', $users, false, ilDBConstants::T_INTEGER);
        }

        $types = [ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER];
        $values = [$module, $sender_id];

        $ilDB->manipulateF($query, $types, $values);
    }

    public static function registerChannel(
        ilDBInterface $db,
        string $name,
        string $title,
        string $description,
        string $class,
        string $classfile,
        string $config_type
    ): void {
        $db->insert(
            ilNotificationSetupHelper::$tbl_notification_channels,
            [
                'channel_name' => [ilDBConstants::T_TEXT, $name],
                'title' => [ilDBConstants::T_TEXT, $title],
                'description' => [ilDBConstants::T_TEXT, $description],
                'class' => [ilDBConstants::T_TEXT, $class],
                'include' => [ilDBConstants::T_TEXT, $classfile],
                'config_type' => [ilDBConstants::T_TEXT, $config_type],
            ]
        );
    }

    public static function registerType(
        ilDBInterface $db,
        string $name,
        string $title,
        string $description,
        string $notification_group,
        string $config_type
    ): void {
        $db->insert(
            ilNotificationSetupHelper::$tbl_notification_types,
            [
                'type_name' => [ilDBConstants::T_TEXT, $name],
                'title' => [ilDBConstants::T_TEXT, $title],
                'description' => [ilDBConstants::T_TEXT, $description],
                'notification_group' => [ilDBConstants::T_TEXT, $notification_group],
                'config_type' => [ilDBConstants::T_TEXT, $config_type],
            ]
        );
    }

    /**
     * @param list<string> $config_types
     * @return array<string, array<string, mixed>>
     */
    public static function getAvailableChannels(array $config_types = [], bool $include_disabled = false): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT channel_name, title, description, class, include, config_type FROM ' . ilNotificationSetupHelper::$tbl_notification_channels;
        if ($config_types) {
            $query .= ' WHERE ' . $ilDB->in('config_type', $config_types, false, ilDBConstants::T_TEXT);
        }

        $rset = $ilDB->query($query);

        $result = [];

        $settings = new ilSetting('notifications');

        while ($row = $ilDB->fetchAssoc($rset)) {
            if (!$include_disabled && !$settings->get('enable_' . $row['channel_name'])) {
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
     * @param list<string> $config_types
     * @return array<string, array<string, mixed>>
     */
    public static function getAvailableTypes(array $config_types = []): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT type_name, title, description, notification_group, config_type FROM ' . ilNotificationSetupHelper::$tbl_notification_types;
        if ($config_types) {
            $query .= ' WHERE ' . $ilDB->in('config_type', $config_types, false, ilDBConstants::T_TEXT);
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
        $types = [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT];
        $values = [$config_name, $type_name];
        $ilDB->manipulateF($query, $types, $values);
    }

    public static function setConfigTypeForChannel(string $channel_name, string $config_name): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'UPDATE ' . ilNotificationSetupHelper::$tbl_notification_channels . ' SET config_type = %s WHERE channel_name = %s';
        $types = [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT];
        $values = [$config_name, $channel_name];
        $ilDB->manipulateF($query, $types, $values);
    }

    /**
     * @param list<int> $usr_ids
     * @return array<int, bool>
     */
    public static function getUsersWithCustomConfig(array $usr_ids): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT usr_id, value FROM usr_pref WHERE ' . $ilDB->in(
            'usr_id',
            $usr_ids,
            false,
            ilDBConstants::T_INTEGER
        ) . ' AND keyword = ' . $ilDB->quote(
            'use_custom_notification_setting',
            ilDBConstants::T_TEXT
        ) . ' AND value = ' . $ilDB->quote(
            '1',
            ilDBConstants::T_TEXT
        );
        $rset = $ilDB->query($query);
        $result = [];
        while ($row = $ilDB->fetchAssoc($rset)) {
            $result[(int) $row['usr_id']] = (bool) $row['value'];
        }

        $missing_usr_ids = array_diff(
            $usr_ids,
            array_keys($result)
        );

        $result = $result + array_combine(
            $missing_usr_ids,
            array_fill(0, count($missing_usr_ids), false)
        );

        return $result;
    }
}
