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

use ILIAS\User\Profile\ProfileChangeMailTokenDBRepository;

class ProfileChangeMailTokenDBRepositoryTest extends ilUserBaseTest
{
    public function testGetNewTokenForUserReturnsMd5OfUserIdAndEmail() : void
    {
        $old_email = 'oldemail@ilias.de';
        $our_token = hash('md5', '5' . '-' . $old_email);
        $new_email = 'newemail@ilias.de';
        $db_mock = $this->createMock(ilDBInterface::class);
        $db_mock->expects($this->once())->method('replace')->willReturn(1);

        $user_mock = $this->createMock(\ilObjUser::class);
        $user_mock->expects($this->once())->method('getId')->willReturn(5);
        $user_mock->expects($this->once())->method('getEmail')->willReturn($old_email);

        $repository = new ProfileChangeMailTokenDBRepository($db_mock);

        $returned_token = $repository->getNewTokenForUser($user_mock, $new_email);

        $this->assertEquals($returned_token, $our_token);
    }

    public function testGetNewEmailForUserReturnsEmail() : void
    {
        $old_email = 'oldemail@ilias.de';
        $our_token = hash('md5', '5' . '-' . $old_email);
        $new_email = 'newemail@ilias.de';
        $db_mock = $this->createMock(ilDBInterface::class);
        $db_mock->expects($this->once())->method('fetchObject')->willReturn((object) ['new_email' => $new_email]);

        $user_mock = $this->createMock(\ilObjUser::class);
        $user_mock->expects($this->once())->method('getId')->willReturn(5);
        $user_mock->expects($this->once())->method('getEmail')->willReturn($old_email);

        $repository = new ProfileChangeMailTokenDBRepository($db_mock);

        $returned_email = $repository->getNewEmailForUser($user_mock, $our_token);

        $this->assertEquals($returned_email, $new_email);
    }

    public function testGetNewEmailForUserReturnsEmptyStringOnWrongToken() : void
    {
        $old_email = 'oldemail@ilias.de';
        $our_token = hash('md5', '5' . '-' . $old_email);

        $db_mock = $this->createMock(ilDBInterface::class);
        $db_mock->expects($this->never())->method('fetchObject');

        $user_mock = $this->createMock(\ilObjUser::class);
        $user_mock->expects($this->once())->method('getId')->willReturn(2);
        $user_mock->expects($this->once())->method('getEmail')->willReturn($old_email);

        $repository = new ProfileChangeMailTokenDBRepository($db_mock);

        $returned_email = $repository->getNewEmailForUser($user_mock, $our_token);

        $this->assertEquals($returned_email, '');
    }
}
