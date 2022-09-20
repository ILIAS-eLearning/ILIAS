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

/**
 * This checks if a mail has to be send after a certain INACTIVITY period
 * @author  Guido Vollbach <gvollbach@databay.de>
 */
class ilCronDeleteInactiveUserReminderMail
{
    public const TABLE_NAME = "usr_cron_mail_reminder";

    private static function mailSent(int $usr_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilDB->manipulateF(
            "INSERT INTO " . self::TABLE_NAME . " (usr_id, ts) VALUES (%s, %s)",
            array(
                "integer",
                "integer"
            ),
            array(
                $usr_id,
                time()
            )
        );
    }

    private static function sendReminder(
        ilObjUser $user,
        int $reminderTime,
        int $time_frame_for_deletion
    ): void {
        $mail = new ilCronDeleteInactiveUserReminderMailNotification();
        $mail->setRecipients(array($user));
        $mail->setAdditionalInformation(
            array(
                 "www" => ilUtil::_getHttpPath(),
                 "days" => $reminderTime,
                 "date" => $time_frame_for_deletion
             )
        );
        $mail->send();
        self::mailSent($user->getId());
    }

    public static function removeEntriesFromTableIfLastLoginIsNewer(): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT usr_id,ts FROM " . self::TABLE_NAME;
        $res = $ilDB->queryF($query, array(
            'integer',
            'integer'
        ), array(
            'usr_id',
            'ts'
        ));
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $lastLoginUnixtime = strtotime(ilObjUser::_lookupLastLogin($row->usr_id));
            $lastReminderSent = (int) $row->ts;
            if ($lastLoginUnixtime >= $lastReminderSent) {
                self::removeSingleUserFromTable($row->usr_id);
            }
        }
    }

    public static function checkIfReminderMailShouldBeSend(
        ilObjUser $user,
        int $reminderTime,
        int $time_frame_for_deletion
    ): bool {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT ts FROM " . self::TABLE_NAME . " WHERE usr_id = %s";
        $res = $ilDB->queryF($query, array('integer'), array($user->getId()));
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        if ($row->ts == null) {
            self::sendReminder($user, $reminderTime, $time_frame_for_deletion);
            return true;
        }
        return false;
    }

    public static function flushDataTable(): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilDB->manipulate("DELETE FROM " . self::TABLE_NAME);
    }

    public static function removeSingleUserFromTable(int $usr_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "DELETE FROM " . self::TABLE_NAME . " WHERE usr_id = %s";
        $ilDB->manipulateF($query, array('integer'), array($usr_id));
    }
}
