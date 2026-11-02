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

use ILIAS\BookingManager\Notification\AdminReservation;
use ILIAS\BookingManager\Notification\PersonalReservation;
use ILIAS\BookingManager\Notification\PoolReservations;
use ILIAS\BookingManager\Notification\ReminderMailText;
use ILIAS\BookingManager\Notification\ReminderSection;
use PHPUnit\Framework\TestCase;

class ilBookCronNotificationTest extends TestCase
{
    public function testPlainTextRendersPersonalAndAdminReservations(): void
    {
        $mail = new ReminderMailText(
            new ReminderSection('book_your_reservations', [
                new PoolReservations(
                    'Test-Pool Reminder',
                    'https://ilias.example/goto.php?target=book_15&client_id=demo',
                    [new PersonalReservation('Raum A', 1, '2026-10-31, 11:40 - 12:00')]
                ),
            ]),
            new ReminderSection('book_reservation_overview', [
                new PoolReservations(
                    'Zweiter Pool',
                    'https://ilias.example/goto.php?target=book_16&client_id=demo',
                    [
                        new AdminReservation('Raum B', 2, 'Rob', '2026-11-01, 09:00 - 09:30', 'book_message: Bitte Schlüssel'),
                        new AdminReservation('Raum C', 1, 'Ann', '2026-11-02, 10:00 - 10:30', ''),
                    ]
                ),
            ])
        );

        $this->assertSame(
            "\nbook_your_reservations\n"
            . "-----------------------------------------\n"
            . "Test-Pool Reminder (https://ilias.example/goto.php?target=book_15&client_id=demo):\n"
            . "- Raum A (1), 2026-10-31, 11:40 - 12:00\n"
            . "\nbook_reservation_overview\n"
            . "-----------------------------------------\n"
            . "Zweiter Pool (https://ilias.example/goto.php?target=book_16&client_id=demo):\n"
            . "- Raum B (2), Rob, 2026-11-01, 09:00 - 09:30\n"
            . "  book_message: Bitte Schlüssel\n"
            . "- Raum C (1), Ann, 2026-11-02, 10:00 - 10:30\n",
            (string) $mail
        );
    }

    public function testPoolReservationsRejectAnEmptyGotoLink(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A pool reminder needs a goto link.');

        new PoolReservations('Ohne Link', '', []);
    }
}
