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

use ILIAS\Refinery\Factory;
use ILIAS\Mail\Attachments\MailAttachments;
use ILIAS\Mail\Service\MailSignatureService;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\Mail\Autoresponder\AutoresponderService;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

class ilMailCopyOnSendTest extends ilMailBaseTestCase
{
    private MockObject&ilFileDataMail $mail_file_data;

    protected function setUp(): void
    {
        parent::setUp();

        $refinery = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('refinery', $refinery);
    }

    public function testDeliveryAttachmentsReturnsLegacyUnchanged(): void
    {
        $mail = $this->createMail();
        $this->mail_file_data->expects($this->never())->method('copyCollectionForDelivery');

        $legacy = MailAttachments::fromLegacyFilenames(['file.pdf']);
        $result = $this->invokeDeliveryAttachments($legacy, $mail);

        $this->assertSame($legacy, $result);
    }

    public function testDeliveryAttachmentsReturnsEmptyUnchanged(): void
    {
        $mail = $this->createMail();
        $this->mail_file_data->expects($this->never())->method('copyCollectionForDelivery');

        $empty = MailAttachments::empty();
        $result = $this->invokeDeliveryAttachments($empty, $mail);

        $this->assertTrue($result->isEmpty());
    }

    public function testDeliveryAttachmentsCopiesIrssCollectionPerCall(): void
    {
        $mail = $this->createMail();
        $source = new ResourceCollectionIdentification('source-rcid');
        $copy_a = new ResourceCollectionIdentification('copy-a');
        $copy_b = new ResourceCollectionIdentification('copy-b');

        $this->mail_file_data
            ->expects($this->exactly(2))
            ->method('copyCollectionForDelivery')
            ->with($source)
            ->willReturnOnConsecutiveCalls($copy_a, $copy_b);

        $first = $this->invokeDeliveryAttachments(MailAttachments::fromIrss($source), $mail);
        $second = $this->invokeDeliveryAttachments(MailAttachments::fromIrss($source), $mail);

        $this->assertSame('copy-a', $first->rcid()->serialize());
        $this->assertSame('copy-b', $second->rcid()->serialize());
    }

    public function testDeliveryAttachmentsReturnsEmptyWhenCopyFails(): void
    {
        $mail = $this->createMail();
        $source = new ResourceCollectionIdentification('source-rcid');
        $this->mail_file_data
            ->expects($this->once())
            ->method('copyCollectionForDelivery')
            ->willReturn(null);

        $result = $this->invokeDeliveryAttachments(MailAttachments::fromIrss($source), $mail);

        $this->assertTrue($result->isEmpty());
    }

    public function testSharedDeliveryAttachmentsReuseSingleCopy(): void
    {
        $mail = $this->createMail();
        $source = new ResourceCollectionIdentification('source-rcid');
        $shared = new ResourceCollectionIdentification('shared-copy');

        $this->mail_file_data
            ->expects($this->once())
            ->method('copyCollectionForDelivery')
            ->with($source)
            ->willReturn($shared);

        $mail->setShareAttachments(true);

        $first = $this->invokeDeliveryAttachments(MailAttachments::fromIrss($source), $mail);
        $second = $this->invokeDeliveryAttachments(MailAttachments::fromIrss($source), $mail);

        $this->assertSame('shared-copy', $first->rcid()->serialize());
        $this->assertSame($first, $second);
    }

    private function createMail(): ilMail
    {
        $this->mail_file_data = $this->getMockBuilder(ilFileDataMail::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['copyCollectionForDelivery'])
            ->getMock();

        return new ilMail(
            6,
            $this->createMock(ilMailAddressTypeFactory::class),
            $this->createMock(ilMailRfc822AddressParserFactory::class),
            $this->createMock(ilAppEventHandler::class),
            $this->createMock(ilLogger::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilLanguage::class),
            $this->mail_file_data,
            $this->createMock(ilMailOptions::class),
            $this->createMock(ilMailbox::class),
            $this->createMock(ilMailMimeSenderFactory::class),
            static fn(string $login): int => 6,
            $this->createMock(AutoresponderService::class),
            0,
            234,
            $this->createMock(ilObjUser::class),
            $this->createMock(ilMailTemplatePlaceholderResolver::class),
            null,
            null,
            $this->createMock(MailSignatureService::class),
        );
    }

    private function invokeDeliveryAttachments(MailAttachments $source, ?ilMail $mail = null): MailAttachments
    {
        $mail ??= $this->createMail();

        $reflection = new ReflectionClass($mail);

        return $reflection->getMethod('getDeliveryAttachments')->invoke($mail, $source);
    }
}
