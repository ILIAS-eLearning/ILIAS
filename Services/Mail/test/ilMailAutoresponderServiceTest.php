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

use ILIAS\Mail\Autoresponder\AutoresponderService;
use ILIAS\Mail\Autoresponder\AutoresponderServiceImpl;
use ILIAS\Mail\Autoresponder\AutoresponderDto;
use ILIAS\Mail\Autoresponder\AutoresponderRepository;
use ILIAS\Data\Clock\ClockInterface;

class ilMailAutoresponderServiceTest extends ilMailBaseTest
{
    private const MAIL_SENDER_USER_ID = 4711;
    private const MAIL_RECEIVER_USER_ID = 4712;

    /**
     * @dataProvider autoresponderProvider
     */
    public function testAutoresponderDeliveryWillBeHandledCorrectlyDependingOnLastSentTime(
        ?DateTimeImmutable $last_auto_responder_time,
        DateTimeImmutable $faked_now,
        int $interval,
        bool $expects_active_auto_responder
    ): void {
        $clock = $this->createMock(ClockInterface::class);
        $clock->method('now')->willReturn($faked_now);

        $repository = $this->createMock(AutoresponderRepository::class);

        if ($last_auto_responder_time === null) {
            $repository->expects($this->once())->method('exists')->willReturn(false);
            $repository->expects($this->never())->method('findBySenderIdAndReceiverId');

            $auto_responder_record = $this->createAutoresponderRecord(
                self::MAIL_SENDER_USER_ID,
                self::MAIL_RECEIVER_USER_ID,
                $faked_now
            );
        } else {
            $auto_responder_record = $this->createAutoresponderRecord(
                self::MAIL_SENDER_USER_ID,
                self::MAIL_RECEIVER_USER_ID,
                $last_auto_responder_time
            );

            $repository->expects($this->once())->method('exists')->willReturn(true);
            $repository->expects($this->once())->method('findBySenderIdAndReceiverId')->willReturn($auto_responder_record);
        }

        if ($expects_active_auto_responder) {
            $repository->expects($this->once())->method('store')->with(
                $this->callback(static function (AutoresponderDto $actual) use ($faked_now, $auto_responder_record): bool {
                    return (
                        // Compare by values, not identity (and ignore sent time)
                        $actual->getReceiverId() === $auto_responder_record->getReceiverId() &&
                        $actual->getSenderId() === $auto_responder_record->getSenderId()
                    ) && $faked_now->format('Y-m-d H:i:s') === $actual->getSentTime()->format('Y-m-d H:i:s');
                })
            );
        } else {
            $repository->expects($this->never())->method('store');
        }

        $mail_receiver_options = $this->getMockBuilder(ilMailOptions::class)->disableOriginalConstructor()->getMock();
        $mail_receiver_options->method('getUsrId')->willReturn(self::MAIL_RECEIVER_USER_ID);
        $mail_receiver_options->method('isAbsent')->willReturn(true);

        $mail_options = $this->getMockBuilder(ilMailOptions::class)->disableOriginalConstructor()->getMock();
        $mail_options->method('getUsrId')->willReturn(self::MAIL_SENDER_USER_ID);
        $mail_options->method('isAbsent')->willReturn(false);


        $auto_responder_service = $this->createService(
            $interval,
            $repository,
            $clock
        );

        $auto_responder_service->enqueueAutoresponderIfEnabled(self::MAIL_SENDER_USER_ID, $mail_receiver_options, $mail_options);

        $auto_responder_service->handleAutoresponderMails(self::MAIL_RECEIVER_USER_ID);
    }

    private function createService(
        int $global_idle_time_interval,
        AutoresponderRepository $auto_responder_repository,
        ClockInterface $clock
    ): AutoresponderService {
        return new AutoresponderServiceImpl(
            $global_idle_time_interval,
            true,
            $auto_responder_repository,
            $clock,
            static function (
                int $sender_id,
                ilMailOptions $receiver_mail_options,
                DateTimeImmutable $next_auto_responder_datetime
            ): void {
            }
        );
    }

    private function createAutoresponderRecord(
        int $auto_responder_sender_usr_id,
        int $auto_responder_receiver_id,
        DateTimeImmutable $sender_time
    ): AutoresponderDto {
        return new AutoresponderDto(
            $auto_responder_sender_usr_id,
            $auto_responder_receiver_id,
            $sender_time
        );
    }

    public function autoresponderProvider(): Generator
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        yield 'Last Sent Date is 1 Second in Future with Disabled Idle Time Interval' => [
            $now->modify('+1 seconds'),
            $now,
            0,
            false,
        ];

        yield 'Last Sent Date is -1 Day in Past with Disabled Idle Time Interval' => [
            $now->sub(new DateInterval('P1D')),
            $now,
            0,
            true,
        ];

        yield 'Last Sent Date is -1 Day + 1 Second in Past with Idle Time Interval being 1 Day' => [
            $now->sub(new DateInterval('P1D'))->modify('+1 second'),
            $now,
            1,
            false,
        ];

        yield 'Last Sent Date is -1 Day in Past with Idle Time Interval being 1 Day' => [
            $now->sub(new DateInterval('P1D')),
            $now,
            1,
            true,
        ];

        yield 'Last Sent Date is -1 Day in Past with an Added Idle Time Interval Exceeding Now' => [
            $now->sub(new DateInterval('P1D')),
            $now,
            2,
            false,
        ];

        yield 'No autoresponder sent, yet' => [
            null,
            $now,
            1,
            true,
        ];
    }
}
