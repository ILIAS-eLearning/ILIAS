<?php declare(strict_types=1);

use ILIAS\Services\Mail\AutoResponder\AutoResponder;

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
class ilAutoResponderTest extends ilMailBaseTest
{

    /**
     * @dataProvider getAutoResponderData
     */
    public function testHasAutoResponderSent(DateTimeImmutable $last_auto_responder_time, DateTimeImmutable $new_autoresponder_time, int $interval, bool $result) : void
    {
        $now = new DateTimeImmutable('NOW');
        $yesterday = $now->sub(new DateInterval('P1D'));
        $autoResponder = $this->create(1, 1, $last_auto_responder_time);
        $this->assertSame($result, $autoResponder->hasAutoResponderSent($new_autoresponder_time, $interval));
    }


    private function create(int $sender_id = 0, int $receiver_id = 0, DateTimeImmutable $sender_time = null) : AutoResponder
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
