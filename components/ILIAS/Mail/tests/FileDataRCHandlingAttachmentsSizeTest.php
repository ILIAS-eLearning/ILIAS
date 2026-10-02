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

use ILIAS\ResourceStorage\Services;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\FileUpload\Handler\FileInfoResult;
use PHPUnit\Framework\Attributes\DataProvider;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

class FileDataRCHandlingAttachmentsSizeTest extends ilMailBaseTestCase
{
    /**
     * @param list<array{id: string, size: int}> $files
     */
    #[DataProvider('exceededLimitProvider')]
    public function testHandleAttachmentsRejectsWhenTotalSizeExceedsLimit(
        ?float $limit,
        array $files
    ): void {
        $subject = $this->createSubject($limit, $files, expect_collection: false);

        $this->expectException(ilMailAttachmentsTotalSizeLimitExceededException::class);
        $subject->handle(array_column($files, 'id'));
    }

    /**
     * @param list<array{id: string, size: int}> $files
     */
    #[DataProvider('acceptedLimitProvider')]
    public function testHandleAttachmentsStoresWhenTotalSizeIsWithinLimit(
        ?float $limit,
        array $files
    ): void {
        $expected_rcid = new ResourceCollectionIdentification('mail-rcid');
        $subject = $this->createSubject($limit, $files, expect_collection: true, expected_rcid: $expected_rcid);

        $result = $subject->handle(array_column($files, 'id'));

        $this->assertSame($expected_rcid, $result);
    }

    /**
     * @return array<string, array{0: ?float, 1: list<array{id: string, size: int}>}>
     */
    public static function exceededLimitProvider(): array
    {
        return [
            'single file above limit' => [5_242_880.0, [['id' => 'a.bin', 'size' => 5_242_881]]],
            'two files above combined limit' => [5_242_880.0, [
                ['id' => 'a.bin', 'size' => 3_000_000],
                ['id' => 'b.bin', 'size' => 3_000_000],
            ]],
        ];
    }

    /**
     * @return array<string, array{0: ?float, 1: list<array{id: string, size: int}>}>
     */
    public static function acceptedLimitProvider(): array
    {
        return [
            'no mail total limit' => [null, [['id' => 'a.bin', 'size' => 10_000_000]]],
            'total below limit' => [5_242_880.0, [['id' => 'a.bin', 'size' => 5_000_000]]],
            'total equals limit' => [5_242_880.0, [['id' => 'a.bin', 'size' => 5_242_880]]],
        ];
    }

    /**
     * @param list<array{id: string, size: int}> $files
     */
    private function createSubject(
        ?float $limit,
        array $files,
        bool $expect_collection,
        ?ResourceCollectionIdentification $expected_rcid = null
    ): FileDataRCHandlingAttachmentsSizeTestSubject {
        $infos = [];
        $resource_identifications = [];
        foreach ($files as $file) {
            $info = $this->createMock(FileInfoResult::class);
            $info->method('getFileIdentifier')->willReturn($file['id']);
            $info->method('getSize')->willReturn($file['size']);
            $info->method('getName')->willReturn($file['id']);
            $infos[$file['id']] = $info;
            $resource_identifications[$file['id']] = new ResourceIdentification($file['id']);
        }

        $fdm = $this->createMock(ilFileDataMail::class);
        $fdm->method('getAttachmentsTotalSizeLimit')->willReturn($limit);
        if ($expect_collection) {
            $fdm->expects($this->once())
                ->method('createCollectionFromResourceIdentifications')
                ->with(array_values($resource_identifications))
                ->willReturn($expected_rcid);
        } else {
            $fdm->expects($this->never())->method('createCollectionFromResourceIdentifications');
        }

        $manager = $this->createMock(Manager::class);
        if ($expect_collection) {
            $manager->expects($this->exactly(count($files)))
                ->method('find')
                ->willReturnCallback(
                    static fn(string $id): ResourceIdentification => $resource_identifications[$id]
                );
        } else {
            $manager->expects($this->never())->method('find');
        }

        $storage = $this->createMock(Services::class);
        $storage->method('manage')->willReturn($manager);

        $upload_handler = $this->createMock(ilMailFormUploadHandlerGUI::class);
        $upload_handler->method('getInfoResult')->willReturnCallback(
            static fn(string $id): FileInfoResult => $infos[$id]
        );

        $lng = $this->createMock(ilLanguage::class);
        $lng->method('txt')->willReturnArgument(0);
        $this->setGlobalVariable('lng', $lng);

        return new FileDataRCHandlingAttachmentsSizeTestSubject($fdm, $upload_handler, $lng, $storage);
    }
}

class FileDataRCHandlingAttachmentsSizeTestSubject
{
    use FileDataRCHandling {
        handleAttachments as public handle;
    }

    public function __construct(
        public ilFileDataMail $fdm,
        public ilMailFormUploadHandlerGUI $upload_handler,
        public ilLanguage $lng,
        public Services $storage
    ) {
    }
}
