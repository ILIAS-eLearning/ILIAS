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

use PHPUnit\Framework\TestCase;
use ILIAS\Mail\Attachments\MailAttachments;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

class ilMailCopyOnSendTest extends TestCase
{
    private MockObject&ilFileDataMail $mail_file_data;

    public function testDeliveryAttachmentsReturnsLegacyUnchanged(): void
    {
        $mail = $this->createMailInstance();
        $this->mail_file_data->expects($this->never())->method('copyCollectionForDelivery');

        $legacy = MailAttachments::fromLegacyFilenames(['file.pdf']);
        $result = $this->invokeDeliveryAttachments($legacy, $mail);

        $this->assertSame($legacy, $result);
    }

    public function testDeliveryAttachmentsReturnsEmptyUnchanged(): void
    {
        $mail = $this->createMailInstance();
        $this->mail_file_data->expects($this->never())->method('copyCollectionForDelivery');

        $empty = MailAttachments::empty();
        $result = $this->invokeDeliveryAttachments($empty, $mail);

        $this->assertTrue($result->isEmpty());
    }

    public function testDeliveryAttachmentsCopiesIrssCollectionPerCall(): void
    {
        $mail = $this->createMailInstance();
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
        $mail = $this->createMailInstance();
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
        $mail = $this->createMailInstance();
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

    private function createMailInstance(): ilMail
    {
        $this->mail_file_data = $this->getMockBuilder(ilFileDataMail::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['copyCollectionForDelivery'])
            ->getMock();

        $mail = (new ReflectionClass(ilMail::class))->newInstanceWithoutConstructor();

        $reflection = new ReflectionClass(ilMail::class);
        $reflection->getProperty('mail_file_data')->setValue($mail, $this->mail_file_data);

        return $mail;
    }

    private function invokeDeliveryAttachments(MailAttachments $source, ilMail $mail): MailAttachments
    {
        $reflection = new ReflectionClass($mail);

        return $reflection->getMethod('getDeliveryAttachments')->invoke($mail, $source);
    }
}
