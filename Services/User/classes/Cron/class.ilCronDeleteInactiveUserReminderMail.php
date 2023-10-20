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

    public function __construct(
        private ilDBInterface $db
    ) {
    }

    public function removeEntriesFromTableIfLastLoginIsNewer(): void
    {
        $query = "SELECT usr_id,ts FROM " . self::TABLE_NAME;
        $res = $this->db->queryF($query, [
            'integer',
            'integer'
        ], [
            'usr_id',
            'ts'
        ]);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $lastLoginUnixtime = strtotime(ilObjUser::_lookupLastLogin($row->usr_id));
            $lastReminderSent = (int) $row->ts;
            if ($lastLoginUnixtime >= $lastReminderSent) {
                $this->removeSingleUserFromTable($row->usr_id);
            }
        }
    }

    public function sendReminderMailIfNeeded(
        ilObjUser $user,
        int $reminderTime,
        int $time_frame_for_deletion
    ): bool {
        $query = "SELECT ts FROM " . self::TABLE_NAME . " WHERE usr_id = %s";
        $res = $this->db->queryF($query, ['integer'], [$user->getId()]);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        if ($row === false || $row->ts === null) {
            $this->sendReminder($user, $reminderTime, $time_frame_for_deletion);
            return true;
        }
        return false;
    }

    public function flushDataTable(): void
    {

        $this->db->manipulate("DELETE FROM " . self::TABLE_NAME);
    }

    public function removeSingleUserFromTable(int $usr_id): void
    {
        $query = "DELETE FROM " . self::TABLE_NAME . " WHERE usr_id = %s";
        $this->db->manipulateF($query, ['integer'], [$usr_id]);
    }

    private function persistMailSent(int $usr_id): void
    {
        $this->db->manipulateF(
            "INSERT INTO " . self::TABLE_NAME . " (usr_id, ts) VALUES (%s, %s)",
            [
                "integer",
                "integer"
            ],
            [
                $usr_id,
                time()
            ]
        );
    }

    private function sendReminder(
        ilObjUser $user,
        int $reminderTime,
        int $time_frame_for_deletion
    ): void {
        $mail = new ilCronDeleteInactiveUserReminderMailNotification();
        $mail->setRecipients([$user]);
        $mail->setAdditionalInformation(
            [
                 "www" => ilUtil::_getHttpPath(),
                 "days" => $reminderTime,
                 "date" => $time_frame_for_deletion
             ]
        );
        $mail->send();
        $this->persistMailSent($user->getId());
    }
}
