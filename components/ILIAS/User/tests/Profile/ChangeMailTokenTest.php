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

namespace ILIAS\User\Tests;

use ILIAS\User\Profile\ChangeMailToken;
use ILIAS\User\Profile\ChangeMailStatus;
use ILIAS\User\Profile\ChangeMailTokenDBRepository;

class ChangeMailTokenTest extends BaseTestCase
{
    public function testGettersReturnCorrectValues(): void
    {
        $user_id = 5;
        $old_email = 'oldemmail@ilias.de';
        $new_email = 'newemail@ilias.de';
        $timestamp = time();
        $status = ChangeMailStatus::Login;
        $token_string = hash('md5', "{$timestamp}-{$user_id}-{$old_email}-{$status->value}");

        $token1 = new ChangeMailToken(
            $user_id,
            $old_email,
            $new_email,
            $timestamp,
            $status,
            $token_string
        );

        $this->assertEquals($user_id, $token1->getUserId());
        $this->assertEquals($old_email, $token1->getCurrentEmail());
        $this->assertEquals($new_email, $token1->getNewEmail());
        $this->assertEquals($timestamp, $token1->getCreatedTimestamp());
        $this->assertEquals($status, $token1->getStatus());
        $this->assertEquals($token_string, $token1->getToken());

        $token2 = new ChangeMailToken(
            $user_id,
            $old_email,
            $new_email,
            $timestamp
        );

        $this->assertEquals($user_id, $token2->getUserId());
        $this->assertEquals($old_email, $token2->getCurrentEmail());
        $this->assertEquals($new_email, $token2->getNewEmail());
        $this->assertEquals($timestamp, $token2->getCreatedTimestamp());
        $this->assertEquals($status, $token2->getStatus());
        $this->assertEquals($token_string, $token2->getToken());
    }

    public function testIsTokenValidForCurrentStatusReturnsCorrectStatus(): void
    {
        $user_id = 5;
        $old_email = 'oldemmail@ilias.de';
        $new_email = 'newemail@ilias.de';

        $token1 = new ChangeMailToken(
            $user_id,
            $old_email,
            $new_email,
            time()
        );

        $this->assertEquals(true, $token1->isTokenValidForCurrentStatus($this->createMock(\ilSetting::class)));

        $token2 = new ChangeMailToken(
            $user_id,
            $old_email,
            $new_email,
            time() - ChangeMailStatus::VALIDITY_LOGIN - 60
        );

        $this->assertEquals(false, $token2->isTokenValidForCurrentStatus($this->createMock(\ilSetting::class)));

        $token3 = new ChangeMailToken(
            $user_id,
            $old_email,
            $new_email,
            time() - \ilRegistrationSettings::REG_HASH_LIFETIME_MIN_VALUE - 60,
            ChangeMailStatus::EmailConfirmation
        );

        $settings_mock = $this->createMock(\ilSetting::class);
        $settings_mock->expects($this->once())->method('get')->willReturn('0');

        $this->assertEquals(false, $token3->isTokenValidForCurrentStatus($settings_mock));
    }
}
