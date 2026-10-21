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

use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\Mail\Attachments\MailAttachments;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class ilFileDataMailPoolTest extends ilMailBaseTestCase
{
    public function testLegacyPoolItemIdentifier(): void
    {
        $file_data_mail = $this->newFileDataMail();

        $this->assertTrue(
            $file_data_mail->isLegacyPoolItemIdentifier('legacy:chef-man-cap-svgrepo-com.svg')
        );
        $this->assertFalse(
            $file_data_mail->isLegacyPoolItemIdentifier('657497dc-5079-4f95-b19d-aecdaf81ff1a')
        );
    }

    public function testLegacyPoolFilenameFromIdentifier(): void
    {
        $file_data_mail = $this->newFileDataMail();

        $this->assertSame(
            'pepper-svgrepo-com.svg',
            $file_data_mail->legacyPoolFilenameFromIdentifier('legacy:pepper-svgrepo-com.svg')
        );
    }

    public function testResolvePoolIdentifiersMigratesLegacyEntries(): void
    {
        $legacy_rid = new ResourceIdentification('legacy-rid');
        $irss_rid = new ResourceIdentification('irss-rid');

        $file_data_mail = $this->getMockBuilder(ilFileDataMail::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['migrateLegacyPoolFilenameToResource'])
            ->getMock();
        $file_data_mail
            ->expects($this->once())
            ->method('migrateLegacyPoolFilenameToResource')
            ->with('old.svg')
            ->willReturn($legacy_rid);

        $irss = $this->createIrssStub([
            'irss-rid' => $irss_rid,
        ]);
        $this->injectIrss($irss, $file_data_mail);

        $resolved = $file_data_mail->resolvePoolIdentifiersToResources([
            'legacy:old.svg',
            'irss-rid',
            'unknown-rid',
        ]);

        $this->assertCount(2, $resolved);
        $this->assertSame('legacy-rid', $resolved[0]->serialize());
        $this->assertSame('irss-rid', $resolved[1]->serialize());
    }

    public function testMigrateLegacyPoolAttachmentsReturnsIrssWhenAlreadyMigrated(): void
    {
        $irss = MailAttachments::fromIrss(
            new ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification('rcid-1')
        );

        $partial = $this->getMockBuilder(ilFileDataMail::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkFilesExist', 'createCollectionFromPoolFilenames'])
            ->getMock();

        $partial->expects($this->never())->method('checkFilesExist');
        $partial->expects($this->never())->method('createCollectionFromPoolFilenames');

        $result = $partial->migrateLegacyPoolAttachments($irss);

        $this->assertSame($irss, $result);
    }

    public function testMigrateLegacyPoolAttachmentsReturnsNullWhenFilesMissing(): void
    {
        $legacy = MailAttachments::fromLegacyFilenames(['missing.svg']);

        $partial = $this->getMockBuilder(ilFileDataMail::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkFilesExist', 'createCollectionFromPoolFilenames'])
            ->getMock();

        $partial->expects($this->once())
            ->method('checkFilesExist')
            ->with(['missing.svg'])
            ->willReturn(false);
        $partial->expects($this->never())->method('createCollectionFromPoolFilenames');

        $this->assertNull($partial->migrateLegacyPoolAttachments($legacy));
    }

    /**
     * @param array<string, ResourceIdentification> $known_rids
     */
    private function createIrssStub(array $known_rids): IRSS
    {
        $manage = $this->createStub(Manager::class);
        $manage->method('find')->willReturnCallback(
            static function (string $rid) use ($known_rids): ?ResourceIdentification {
                return $known_rids[$rid] ?? null;
            }
        );

        $irss = $this->createStub(IRSS::class);
        $irss->method('manage')->willReturn($manage);

        return $irss;
    }

    private function newFileDataMail(): ilFileDataMail
    {
        return (new ReflectionClass(ilFileDataMail::class))->newInstanceWithoutConstructor();
    }

    private function injectIrss(IRSS $irss, ilFileDataMail $file_data_mail): void
    {
        $reflection = new ReflectionClass(ilFileDataMail::class);
        $property = $reflection->getProperty('irss');
        $property->setValue($file_data_mail, $irss);
    }
}
