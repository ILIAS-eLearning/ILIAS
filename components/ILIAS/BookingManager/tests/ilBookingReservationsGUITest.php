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

use PHPUnit\Framework\Attributes\DataProvider;

class ilBookingReservationsCancelLabelProbe extends ilBookingReservationsGUI
{
    public function __construct()
    {
    }

    public function label(string $object_title, int $participant_id, ?string $period): string
    {
        return $this->getCancelConfirmationLabel($object_title, $participant_id, $period);
    }
}

class ilBookingReservationsGUITest extends ilBookingManagerBaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $db = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $db->expects($this->any())
            ->method('in')
            ->willReturn('a.usr_id IN (6)');

        $db->expects($this->any())
            ->method('queryF')
            ->willReturn($this->createMock(ilDBStatement::class));

        $db->expects($this->any())
            ->method('fetchObject')
            ->willReturnOnConsecutiveCalls(
                (object) [
                    'usr_id' => 6,
                    'firstname' => 'Jane',
                    'lastname' => 'Doe',
                    'title' => '',
                    'login' => 'jdoe',
                    'public_profile' => 'y',
                    'public_title' => 'n',
                ],
                null
            );

        $this->setGlobalVariable('ilDB', $db);
    }

    public static function provideCancelConfirmationLabels(): array
    {
        $participant_id = 6;
        $name = 'Doe, Jane [jdoe]';
        $period = '01. Jan 2026, 10:00 - 11:00';

        return [
            'booking without a schedule' => [
                'Microscope',
                $participant_id,
                null,
                "Microscope – {$name}",
            ],
            'booking with a period' => [
                'Microscope',
                $participant_id,
                $period,
                "Microscope – {$name} – {$period}",
            ],
            'blank period' => [
                'Microscope',
                $participant_id,
                '',
                "Microscope – {$name}",
            ],
        ];
    }

    #[DataProvider('provideCancelConfirmationLabels')]
    public function testCancelConfirmationNamesTheParticipant(
        string $object_title,
        int $participant_id,
        ?string $period,
        string $expected
    ): void {
        $this->assertSame(
            $expected,
            (new ilBookingReservationsCancelLabelProbe())->label(
                $object_title,
                $participant_id,
                $period
            )
        );
    }
}
