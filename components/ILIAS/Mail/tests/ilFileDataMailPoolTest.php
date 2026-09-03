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
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class ilFileDataMailPoolTest extends ilMailBaseTestCase
{
    private MockObject&ilFileDataMail $file_data_mail;

    protected function setUp(): void
    {
        parent::setUp();

        $this->file_data_mail = $this->getMockBuilder(ilFileDataMail::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['migrateLegacyPoolFilenameToResource'])
            ->getMock();
    }

    public function testLegacyPoolItemIdentifier(): void
    {
        $this->assertTrue(
            $this->file_data_mail->isLegacyPoolItemIdentifier('legacy:chef-man-cap-svgrepo-com.svg')
        );
        $this->assertFalse(
            $this->file_data_mail->isLegacyPoolItemIdentifier('657497dc-5079-4f95-b19d-aecdaf81ff1a')
        );
    }

    public function testLegacyPoolFilenameFromIdentifier(): void
    {
        $this->assertSame(
            'pepper-svgrepo-com.svg',
            $this->file_data_mail->legacyPoolFilenameFromIdentifier('legacy:pepper-svgrepo-com.svg')
        );
    }

    public function testResolvePoolIdentifiersMigratesLegacyEntries(): void
    {
        $legacy_rid = new ResourceIdentification('legacy-rid');
        $irss_rid = new ResourceIdentification('irss-rid');

        $this->file_data_mail
            ->expects($this->once())
            ->method('migrateLegacyPoolFilenameToResource')
            ->with('old.svg')
            ->willReturn($legacy_rid);

        $irss = $this->createIrssMock([
            'irss-rid' => $irss_rid,
        ]);
        $this->injectIrss($irss);

        $resolved = $this->file_data_mail->resolvePoolIdentifiersToResources([
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
    private function createIrssMock(array $known_rids): IRSS&MockObject
    {
        $manage = $this->createMock(Manager::class);
        $manage->method('find')->willReturnCallback(
            static function (string $rid) use ($known_rids): ?ResourceIdentification {
                return $known_rids[$rid] ?? null;
            }
        );

        $irss = $this->getMockBuilder(IRSS::class)->disableOriginalConstructor()->getMock();
        $irss->method('manage')->willReturn($manage);

        return $irss;
    }

    private function injectIrss(IRSS $irss): void
    {
        $reflection = new ReflectionClass(ilFileDataMail::class);
        $property = $reflection->getProperty('irss');
        $property->setValue($this->file_data_mail, $irss);
    }
}
