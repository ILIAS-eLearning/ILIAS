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

use ILIAS\LegalDocuments\Conductor;
use ILIAS\Refinery\Transformation;
use ILIAS\Data\Result;
use ILIAS\Mail\Recipient;

class RecipientTest extends ilMailBaseTest
{
    public function testCreate(): void
    {
        $user_id = 10;
        $user = $this->createMock(ilObjUser::class);
        $mail_options = $this->createMock(ilMailOptions::class);
        $recipient = new Recipient($user_id, $user, $mail_options, $this->createMock(Conductor::class));
        $this->assertEquals($user_id, $recipient->getUserId());
        $this->assertSame($mail_options, $recipient->getMailOptions());

        $recipient = new Recipient($user_id, null, $mail_options, $this->createMock(Conductor::class));
        $this->assertEquals($user_id, $recipient->getUserId());
        $this->assertSame($mail_options, $recipient->getMailOptions());
    }

    public function testCheckProperties(): void
    {
        $user_id = 10;
        $mail = "mail@test.de";
        $mail_2 = "mail2@test.de";
        $external_mails = [$mail, $mail_2];

        $result = $this->createMock(Result::class);

        $transformation = $this->createMock(Transformation::class);
        $transformation->expects(self::once())->method('applyTo')->willReturn($result);

        $legal_documents = $this->createMock(Conductor::class);
        $legal_documents->expects(self::once())->method('userCanReadInternalMail')->willReturn($transformation);

        $user = $this->createMock(ilObjUser::class);
        $user->expects($this->once())
             ->method("getActive")
             ->willReturn(true);
        $user->expects($this->once())
             ->method("checkTimeLimit")
             ->willReturn(true);

        $mail_options = $this->createMock(ilMailOptions::class);
        $mail_options->expects($this->exactly(3))
                     ->method("getIncomingType")
                     ->willReturn(2);

        $mail_options->expects($this->atLeastOnce())
                     ->method("getExternalEmailAddresses")
                     ->willReturn($external_mails);

        $recipient = new Recipient($user_id, $user, $mail_options, $legal_documents);
        $this->assertEquals($user_id, $recipient->getUserId());
        $this->assertTrue($recipient->isUser());
        $this->assertSame($result, $recipient->evaluateInternalMailReadability());
        $this->assertTrue($recipient->isUserActive());

        $this->assertSame($mail_options, $recipient->getMailOptions());
        $this->assertTrue($recipient->userWantsToReceiveExternalMails());
        $this->assertFalse($recipient->onlyToExternalMailAddress());
        $this->assertIsArray($recipient->getExternalMailAddress());
        $this->assertCount(2, $recipient->getExternalMailAddress());
        $this->assertContainsOnly('string', $recipient->getExternalMailAddress());
    }

    public function testPropertiesPart2(): void
    {
        $user_id = 133;
        $mail = "mails@test.de";
        $mail_2 = "mails2@test.de";
        $external_mails = [$mail, $mail_2];

        $legal_documents = $this->createMock(Conductor::class);
        $legal_documents->expects(self::never())->method('userCanReadInternalMail');

        $user = $this->createMock(ilObjUser::class);
        $user->expects($this->once())
             ->method("getActive")
             ->willReturn(false);
        $user->expects($this->once())
             ->method("checkTimeLimit")
             ->willReturn(false);

        $mail_options = $this->createMock(ilMailOptions::class);
        $mail_options->expects($this->exactly(3))
                     ->method("getIncomingType")
                     ->willReturn(0);

        $mail_options->expects($this->atLeastOnce())
                     ->method("getExternalEmailAddresses")
                     ->willReturn($external_mails);

        $recipient = new Recipient($user_id, $user, $mail_options, $legal_documents);
        $this->assertEquals($user_id, $recipient->getUserId());
        $this->assertTrue($recipient->isUser());
        $result = $recipient->evaluateInternalMailReadability();
        $this->assertFalse($result->isOk());
        $this->assertSame('Account expired.', $result->error());
        $this->assertFalse($recipient->isUserActive());

        $this->assertSame($mail_options, $recipient->getMailOptions());
        $this->assertFalse($recipient->userWantsToReceiveExternalMails());
        $this->assertFalse($recipient->onlyToExternalMailAddress());
        $this->assertIsArray($recipient->getExternalMailAddress());
        $this->assertCount(2, $recipient->getExternalMailAddress());
        $this->assertContainsOnly('string', $recipient->getExternalMailAddress());
    }
}
