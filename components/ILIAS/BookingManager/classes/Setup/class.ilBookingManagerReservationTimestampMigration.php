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

use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class ilBookingManagerReservationTimestampMigration implements Migration
{
    private ilDBInterface $db;

    public function getLabel(): string
    {
        return 'Normalize booking reservation timestamps to minute boundaries and linked personal calendar ends.';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1000;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective(),
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment): void
    {
        $set = $this->db->query(
            'SELECT booking_reservation_id, user_id, date_from, date_to FROM booking_reservation'
            . ' WHERE (date_from > 0 AND MOD(date_from, 60) <> 0)'
            . ' OR (date_to > 0 AND MOD(date_to, 60) <> 0)'
            . ' LIMIT 1'
        );

        $row = $this->db->fetchAssoc($set);
        if ($row === null) {
            return;
        }

        $updates = [];
        $normalized_from = $this->normalizeTimestamp((int) $row['date_from']);
        if ($normalized_from !== (int) $row['date_from']) {
            $updates['date_from'] = [ilDBConstants::T_INTEGER, $normalized_from];
        }

        $normalized_to = $this->normalizeTimestamp((int) $row['date_to']);
        if ($normalized_to !== (int) $row['date_to']) {
            $updates['date_to'] = [ilDBConstants::T_INTEGER, $normalized_to];
        }

        if ($updates === []) {
            return;
        }

        if (isset($updates['date_to'])) {
            $this->updateLinkedPersonalCalendarEnd(
                (int) $row['booking_reservation_id'],
                (int) $row['user_id'],
                (int) $row['date_to'],
                $normalized_to
            );
        }

        $this->db->update(
            'booking_reservation',
            $updates,
            [
                'booking_reservation_id' => [ilDBConstants::T_INTEGER, (int) $row['booking_reservation_id']],
            ]
        );
    }

    public function getRemainingAmountOfSteps(): int
    {
        $set = $this->db->query(
            'SELECT COUNT(booking_reservation_id) AS cnt FROM booking_reservation'
            . ' WHERE (date_from > 0 AND MOD(date_from, 60) <> 0)'
            . ' OR (date_to > 0 AND MOD(date_to, 60) <> 0)'
        );

        return (int) ($this->db->fetchObject($set)?->cnt ?? 0);
    }

    private function normalizeTimestamp(int $timestamp): int
    {
        if ($timestamp <= 0) {
            return $timestamp;
        }

        $remainder = $timestamp % 60;
        if ($remainder === 0) {
            return $timestamp;
        }

        return $timestamp + (60 - $remainder);
    }

    private function updateLinkedPersonalCalendarEnd(
        int $booking_reservation_id,
        int $user_id,
        int $legacy_date_to,
        int $normalized_date_to
    ): void {
        if ($legacy_date_to <= 0) {
            return;
        }

        $format = 'Y-m-d H:i:s';
        $this->db->manipulateF(
            <<<SQL
                UPDATE cal_entries
                INNER JOIN cal_cat_assignments ON cal_entries.cal_id = cal_cat_assignments.cal_id
                INNER JOIN cal_categories ON cal_cat_assignments.cat_id = cal_categories.cat_id
                SET cal_entries.enda = %s
                WHERE cal_entries.context_id = %s
                AND cal_categories.type = %s
                AND cal_categories.obj_id = %s
                AND cal_entries.enda = %s
            SQL,
            [
                ilDBConstants::T_DATETIME,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_INTEGER,
                ilDBConstants::T_DATETIME,
            ],
            [
                gmdate($format, $normalized_date_to),
                $booking_reservation_id,
                ilCalendarCategory::TYPE_BOOK,
                $user_id,
                gmdate($format, $legacy_date_to),
            ]
        );
    }
}
