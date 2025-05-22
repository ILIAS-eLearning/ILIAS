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

namespace ILIAS\BookingManager\Settings;

use ilDBInterface;
use ILIAS\BookingManager\InternalDataService;

class SettingsDBRepository
{
    public function __construct(
        protected ilDBInterface $db,
        protected InternalDataService $data
    ) {
    }

    public function create(Settings $settings): void
    {
        $this->db->insert('booking_settings', [
            'booking_pool_id' => ['integer', $settings->getId()],
            'public_log' => ['integer', (int) $settings->getPublicLog()],
            'schedule_type' => ['integer', $settings->getScheduleType()],
            'ovlimit' => ['integer', $settings->getOverallLimit()],
            'rsv_filter_period' => ['integer', $settings->getReservationPeriod()],
            'reminder_status' => ['integer', $settings->getReminderStatus()],
            'reminder_day' => ['integer', $settings->getReminderDay()],
            'pref_deadline' => ['integer', $settings->getPrefDeadline()],
            'preference_nr' => ['integer', $settings->getPreferenceNr()],
            'messages' => ['integer', (int) $settings->getMessages()],
        ]);
    }

    public function update(Settings $settings): void
    {
        $this->db->update('booking_settings', [
            'public_log' => ['integer', (int) $settings->getPublicLog()],
            'schedule_type' => ['integer', $settings->getScheduleType()],
            'ovlimit' => ['integer', $settings->getOverallLimit()],
            'rsv_filter_period' => ['integer', $settings->getReservationPeriod()],
            'reminder_status' => ['integer', $settings->getReminderStatus()],
            'reminder_day' => ['integer', $settings->getReminderDay()],
            'pref_deadline' => ['integer', $settings->getPrefDeadline()],
            'preference_nr' => ['integer', $settings->getPreferenceNr()],
            'messages' => ['integer', (int) $settings->getMessages()],
        ], [
            'booking_pool_id' => ['integer', $settings->getId()],
        ]);
    }

    public function getById(int $id): ?Settings
    {
        $set = $this->db->queryF(
            'SELECT * FROM booking_settings WHERE booking_pool_id = %s',
            ['integer'],
            [$id]
        );

        $record = $this->db->fetchAssoc($set);
        if ($record) {
            return $this->getSettingsFromRecord($record);
        }

        return null;
    }

    public function delete(int $id): void
    {
        $this->db->manipulateF(
            'DELETE FROM booking_settings WHERE booking_pool_id = %s',
            ['integer'],
            [$id]
        );
    }

    protected function getSettingsFromRecord(array $record): Settings
    {
        return $this->data->settings(
            (int) $record['booking_pool_id'],
            (bool) $record['public_log'],
            (int) $record['schedule_type'],
            (int) $record['ovlimit'],
            (int) $record['rsv_filter_period'],
            (bool) $record['reminder_status'],
            (int) $record['reminder_day'],
            (int) $record['pref_deadline'],
            (int) $record['preference_nr'],
            (bool) $record['messages']
        );
    }
}
