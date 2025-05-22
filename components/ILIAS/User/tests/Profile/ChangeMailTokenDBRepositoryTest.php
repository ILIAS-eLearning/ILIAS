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

class ChangeMailTokenDBRepositoryTest extends BaseTestCase
{
    public function testGetNewTokenForUserReturnsCorrectToken(): void
    {
        $timestamp = time() + 1000;
        $user_id = 5;
        $old_email = 'oldemail@ilias.de';
        $status = ChangeMailStatus::Login;
        $token_string = hash('md5', "{$timestamp}-{$user_id}-{$old_email}-{$status->value}");
        $new_email = 'newemail@ilias.de';
        $our_token = new ChangeMailToken(
            $user_id,
            $old_email,
            $new_email,
            $timestamp,
            $status,
            $token_string
        );

        $db_mock = $this->createMock(\ilDBInterface::class);
        $db_mock->expects($this->once())->method('replace')->willReturn(1);

        $user_mock = $this->createMock(\ilObjUser::class);
        $user_mock->expects($this->once())->method('getId')->willReturn(5);
        $user_mock->expects($this->once())->method('getEmail')->willReturn($old_email);

        $repository = new ChangeMailTokenDBRepository($db_mock, $this->createMock(\ilSetting::class));

        $returned_token = $repository->getNewTokenForUser($user_mock, $new_email, $timestamp);

        $this->assertEquals($our_token, $returned_token);
    }

    public function testGetTokenForTokenStringReturnsCorrectToken(): void
    {
        $timestamp = time() + 1000;
        $user_id = 5;
        $old_email = 'oldemail@ilias.de';
        $status = ChangeMailStatus::EmailConfirmation;
        $token_string = hash('md5', "{$timestamp}-{$user_id}-{$old_email}-{$status->value}");
        $new_email = 'newemail@ilias.de';
        $our_token = new ChangeMailToken(
            $user_id,
            $old_email,
            $new_email,
            $timestamp,
            $status,
            $token_string
        );

        $db_mock = $this->createMock(\ilDBInterface::class);
        $db_mock->expects($this->once())->method('fetchObject')->willReturn(
            (object) [
                'token' => $token_string,
                'new_email' => $new_email,
                'status' => $status->value,
                'created_ts' => $timestamp
            ]
        );

        $user_mock = $this->createMock(\ilObjUser::class);
        $user_mock->expects($this->once())->method('getId')->willReturn($user_id);
        $user_mock->expects($this->once())->method('getEmail')->willReturn($old_email);

        $repository = new ChangeMailTokenDBRepository($db_mock, $this->createMock(\ilSetting::class));

        $token = $repository->getTokenForTokenString($token_string, $user_mock);

        $this->assertEquals($our_token, $token);
    }

    public function testGetTokenForTokenStringReturnsNullOnInvalidToken(): void
    {
        $timestamp = time() + 1000;
        $user_id = 5;
        $old_email = 'oldemail@ilias.de';
        $status = ChangeMailStatus::EmailConfirmation;
        $token_string = hash('md5', "{$timestamp}-{$user_id}-{$old_email}-{$status->value}");
        $new_email = 'newemail@ilias.de';

        $db_mock = $this->createMock(\ilDBInterface::class);
        $db_mock->expects($this->once())->method('fetchObject')->willReturn(
            (object) [
                'token' => $token_string,
                'new_email' => $new_email,
                'status' => $status->value,
                'created_ts' => $timestamp
            ]
        );

        $user_mock = $this->createMock(\ilObjUser::class);
        $user_mock->expects($this->once())->method('getId')->willReturn(2);
        $user_mock->expects($this->once())->method('getEmail')->willReturn($old_email);

        $repository = new ChangeMailTokenDBRepository($db_mock, $this->createMock(\ilSetting::class));

        $token = $repository->getTokenForTokenString($token_string, $user_mock);

        $this->assertEquals(null, $token);
    }

    public function testGetTokenForTokenStringReturnsNullOnExpiredToken(): void
    {
        $timestamp = time() - \ilRegistrationSettings::REG_HASH_LIFETIME_MIN_VALUE - 1;
        $user_id = 5;
        $old_email = 'oldemail@ilias.de';
        $status = ChangeMailStatus::EmailConfirmation;
        $token_string = hash('md5', "{$timestamp}-{$user_id}-{$old_email}-{$status->value}");
        $new_email = 'newemail@ilias.de';

        $db_mock = $this->createMock(\ilDBInterface::class);
        $db_mock->expects($this->once())->method('fetchObject')->willReturn(
            (object) [
                'token' => $token_string,
                'new_email' => $new_email,
                'status' => $status->value,
                'created_ts' => $timestamp
            ]
        );

        $user_mock = $this->createMock(\ilObjUser::class);
        $user_mock->expects($this->once())->method('getId')->willReturn($user_id);
        $user_mock->expects($this->once())->method('getEmail')->willReturn($old_email);

        $settings_mock = $this->createMock(\ilSetting::class);
        $settings_mock->expects($this->once())->method('get')->willReturn('0');

        $repository = new ChangeMailTokenDBRepository($db_mock, $settings_mock);

        $token = $repository->getTokenForTokenString($token_string, $user_mock);

        $this->assertEquals(null, $token);
    }

    public function testMoveToNextStepReturnsCorrectToken(): void
    {
        $timestamp = time() + 1000;
        $user_id = 5;
        $old_email = 'oldemail@ilias.de';
        $status = ChangeMailStatus::EmailConfirmation;
        $token_string = hash('md5', "{$timestamp}-{$user_id}-{$old_email}-{$status->value}");
        $new_email = 'newemail@ilias.de';

        $expected_token = new ChangeMailToken(
            $user_id,
            $old_email,
            $new_email,
            $timestamp,
            $status,
            $token_string
        );

        $db_mock = $this->createMock(\ilDBInterface::class);
        $db_mock->expects($this->once())->method('manipulateF')->willReturn(0);
        $db_mock->expects($this->once())->method('replace')->willReturn(0);

        $repository = new ChangeMailTokenDBRepository($db_mock, $this->createMock(\ilSetting::class));

        $new_token = $repository->moveToNextStep(
            new ChangeMailToken(
                $user_id,
                $old_email,
                $new_email,
                123,
                ChangeMailStatus::Login,
                'abc'
            ),
            $timestamp
        );

        $this->assertEquals($expected_token, $new_token);
    }

    public function testMoveToNextStepThrowsErrorIfNotExists(): void
    {
        $user_id = 5;
        $old_email = 'oldemail@ilias.de';

        $db_mock = $this->createMock(\ilDBInterface::class);
        $db_mock->expects($this->never())->method('manipulateF');
        $db_mock->expects($this->never())->method('replace');

        $repository = new ChangeMailTokenDBRepository($db_mock, $this->createMock(\ilSetting::class));

        $this->expectException(\Exception::class);
        $repository->moveToNextStep(
            new ChangeMailToken(
                $user_id,
                $old_email,
                'newemail@ilias.de',
                123,
                ChangeMailStatus::EmailConfirmation,
                'abc'
            ),
            time() + 1000
        );
    }
}
