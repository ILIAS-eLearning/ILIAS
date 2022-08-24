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

/**
 * Class for global mail information (e.g. in main menu).
 * This class should only contain methods for fetching data which is necessary in global parts of ILIAS,
 * e.g. the main menu.
 * We should keep this class as small as possible.
 * Maybe we duplicate some code which already exists in class ilMail, but we need an efficient class.
 * @author    Michael Jansen <mjansen@databay.de>
 */
class ilMailGlobalServices
{
    public const CACHE_TYPE_REF_ID = 0;
    public const CACHE_TYPE_NEW_MAILS = 1;
    protected static array $global_mail_services_cache = [];

    public static function getMailObjectRefId(): int
    {
        global $DIC;

        if (isset(self::$global_mail_services_cache[self::CACHE_TYPE_REF_ID]) &&
            null !== self::$global_mail_services_cache[self::CACHE_TYPE_REF_ID]) {
            return (int) self::$global_mail_services_cache[self::CACHE_TYPE_REF_ID];
        }

        // mail settings id is set by a constant in ilias.ini.
        // Keep the select for some time until everyone has updated his ilias.ini
        if (!MAIL_SETTINGS_ID) {
            $res = $DIC->database()->queryF(
                '
				SELECT object_reference.ref_id FROM object_reference, tree, object_data
				WHERE tree.parent = %s
				AND object_data.type = %s
				AND object_reference.ref_id = tree.child
				AND object_reference.obj_id = object_data.obj_id',
                ['integer', 'text'],
                [SYSTEM_FOLDER_ID, 'mail']
            );

            while ($row = $DIC->database()->fetchAssoc($res)) {
                self::$global_mail_services_cache[self::CACHE_TYPE_REF_ID] = (int) $row['ref_id'];
            }
        } else {
            self::$global_mail_services_cache[self::CACHE_TYPE_REF_ID] = MAIL_SETTINGS_ID;
        }

        return (int) self::$global_mail_services_cache[self::CACHE_TYPE_REF_ID];
    }

    /**
     * @param ilObjUser $user
     * @param int $leftInterval
     * @return array{count: int, max_time: string}
     */
    public static function getNewMailsData(ilObjUser $user, int $leftInterval = 0): array
    {
        global $DIC;

        if ($user->isAnonymous() || 0 === $user->getId()) {
            return [
                'count' => 0,
                'max_time' => (new DateTimeImmutable('@' . time()))->format('Y-m-d H:i:s')
            ];
        }

        $cacheKey = implode('_', [self::CACHE_TYPE_NEW_MAILS, $user->getId(), $leftInterval]);

        if (
            isset(self::$global_mail_services_cache[$cacheKey]) &&
            null !== self::$global_mail_services_cache[$cacheKey]) {
            return self::$global_mail_services_cache[$cacheKey];
        }

        $query = '
            SELECT COUNT(mail_id) cnt, MAX(send_time) send_time
            FROM mail
            WHERE folder_id = %s AND user_id = %s AND m_status = %s
        ';
        if ($leftInterval > 0) {
            $query .= ' AND send_time > '
                . $DIC->database()->quote(date('Y-m-d H:i:s', $leftInterval), 'timestamp');
        }

        $res = $DIC->database()->queryF(
            $query,
            ['integer', 'integer', 'text'],
            [0, $user->getId(), 'unread']
        );
        $row = $DIC->database()->fetchAssoc($res);

        $query = '
            SELECT COUNT(mail_id) cnt, MAX(m.send_time) send_time
            FROM mail m
            INNER JOIN mail_obj_data mo
                ON mo.user_id = m.user_id
                AND mo.obj_id = m.folder_id
                AND mo.m_type = %s
            WHERE m.user_id = %s
	 		AND m.m_status = %s';
        if ($leftInterval > 0) {
            $query .= ' AND m.send_time > '
                . $DIC->database()->quote(date('Y-m-d H:i:s', $leftInterval), 'timestamp');
        }

        $res = $DIC->database()->queryF(
            $query,
            ['text', 'integer', 'text'],
            ['inbox', $user->getId(), 'unread']
        );
        $row2 = $DIC->database()->fetchAssoc($res);

        self::$global_mail_services_cache[$cacheKey] = [
            'count' => ((int) $row['cnt'] + (int) $row2['cnt']),
            'max_time' => max(
                (string) $row['send_time'],
                (string) $row2['send_time']
            ),
        ];

        return self::$global_mail_services_cache[$cacheKey];
    }
}
