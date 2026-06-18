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
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

class MailAttachmentsTest extends TestCase
{
    public function testParseRcidString(): void
    {
        $rcid = new ResourceCollectionIdentification('rcid-123');
        $parsed = MailAttachments::fromDb($rcid->serialize());

        $this->assertInstanceOf(MailAttachments::class, $parsed);
        $this->assertTrue($parsed->isIrss());
        $this->assertSame('rcid-123', $parsed->rcid()->serialize());
    }

    public function testParseLegacyArray(): void
    {
        $parsed = MailAttachments::fromDb(serialize(['file.pdf', 'image.png']));

        $this->assertTrue($parsed->isLegacy());
        $this->assertSame(['file.pdf', 'image.png'], $parsed->legacyFilenames());
    }

    public function testSerializeRcidForBackgroundTask(): void
    {
        $attachments = MailAttachments::fromIrss(new ResourceCollectionIdentification('rcid-456'));
        $parsed = MailAttachments::fromBackgroundTask($attachments->toBackgroundTask());

        $this->assertTrue($parsed->isIrss());
        $this->assertSame('rcid-456', $parsed->rcid()->serialize());
    }

    public function testIsEmpty(): void
    {
        $this->assertTrue(MailAttachments::empty()->isEmpty());
        $this->assertFalse(MailAttachments::fromIrss(new ResourceCollectionIdentification('rcid-789'))->isEmpty());
    }
}
