<?php declare(strict_types=1);

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

use ILIAS\Services\Mail\AutoResponder\AutoResponderService;
use ILIAS\Services\Mail\AutoResponder\AutoResponderServiceImpl;
use ILIAS\Services\Mail\AutoResponder\AutoResponder;
use ILIAS\Services\Mail\AutoResponder\AutoResponderRepository;
use ILIAS\Data\Clock\ClockInterface;

class ilAutoResponderServiceTest extends ilMailBaseTest
{
    private function create(
        callable $loginByUsrIdCallable,
        int $global_idle_time_interval,
        bool $auto_responder_status,
        array $auto_responder_data,
        AutoResponderRepository $auto_responder_repository,
        ClockInterface $clock
    ) : AutoResponderService {
        $this->setGlobalVariable('ilDB', null);
        return new AutoResponderServiceImpl(
            $loginByUsrIdCallable,
            $global_idle_time_interval,
            $auto_responder_status,
            $auto_responder_data,
            $auto_responder_repository,
            $clock
        );
    }


    /**
     * @dataProvider getAutoResponderData
     */
    public function testHasAutoResponderSent(DateTimeImmutable $last_auto_responder_time, DateTimeImmutable $new_autoresponder_time, int $interval, bool $result) : void
    {
        $now = new DateTimeImmutable('NOW');
        $yesterday = $now->sub(new DateInterval('P1D'));
        $clock = $this->getMockBuilder(ClockInterface::class)->disableOriginalConstructor()->getMock();
        $clock->method('now')->willReturn($new_autoresponder_time);
        $auto_responder_service = $this->create(
            static function (int $usrId) : string {
                return ilObjUser::_lookupLogin($usrId);
            },
            $interval,
            true,
            [],
            $this->getMockBuilder(AutoResponderRepository::class)->disableOriginalConstructor()->getMock(),
            $clock
        );
        $auto_responder = $this->createAutoResponder(1, 1, $last_auto_responder_time);
        $this->assertSame($result, $auto_responder_service->shouldSendAutoResponder($auto_responder, $interval));
    }


    private function createAutoResponder(int $sender_id = 0, int $receiver_id = 0, DateTimeImmutable $sender_time = null) : AutoResponder
    {
        return new AutoResponder($sender_id, $receiver_id, $sender_time ?? new DateTimeImmutable('NOW'));
    }

    public function getAutoResponderData() : Generator
    {
        yield 'timespan negative' => [
            new DateTimeImmutable('NOW'),
            (new DateTimeImmutable('NOW'))->sub(new DateInterval('P1D')),
            0,
            true,
        ];

        yield 'timespan positive interval smaller than timespan' => [
            new DateTimeImmutable('NOW'),
            (new DateTimeImmutable('NOW'))->add(new DateInterval('P1D')),
            0,
            false,
        ];

        yield 'timespan positive interval same as timespan' => [
            new DateTimeImmutable('NOW'),
            (new DateTimeImmutable('NOW'))->add(new DateInterval('P1D')),
            1,
            false,
        ];

        yield 'timespan positive interval bigger than timespan' => [
            new DateTimeImmutable('NOW'),
            (new DateTimeImmutable('NOW'))->add(new DateInterval('P1D')),
            2,
            true,
        ];
    }
}
