<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\Collection\ImmutableStringMap;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\FileUpload;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateBackgroundImageUploadTest extends ilCertificateBaseTestCase
{
    public function testFileCanBeUploaded()
    {
        $fileUpload = $this->getMockBuilder(FileUpload::class)
            ->getMock();

        $fileUpload->method('hasBeenProcessed')
            ->willReturn(false);

        $fileUpload->expects($this->once())
            ->method('process');

        $fileUpload->method('hasUploads')
            ->willReturn(true);

        $uploadResult = new UploadResult(
            'phpunit',
            1024,
            'text/xml',
            $this->getMockBuilder(ImmutableStringMap::class)->getMock(),
            new ProcessingStatus(ProcessingStatus::OK, 'uploaded'),
            '/tmp'
        );

        $fileUpload->method('getResults')
            ->willReturn(["some/where/temporary" => $uploadResult]);

        $fileUpload->expects($this->once())
            ->method('moveOneFileTo');

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileSystem = $this->getMockBuilder(Filesystem::class)
            ->getMock();

        $fileSystem->method('hasDir')
            ->willReturn(true);

        $fileSystem->method('has')
            ->willReturn(true);

        $fileSystem->expects($this->once())
            ->method('delete');

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $utilHelper->expects($this->exactly(2))
            ->method('convertImage');

        $fileUtilsHelper = $this->getMockBuilder(ilCertificateFileUtilsHelper::class)
            ->getMock();

        $legacyPathHelper = $this->getMockBuilder(LegacyPathHelperHelper::class)
            ->getMock();

        $legacyPathHelper->method('createRelativePath')
            ->willReturn('the/relative/path');

        $upload = new ilCertificateBackgroundImageUpload(
            $fileUpload,
            'certifcate/path/to/some/where',
            $language,
            $logger,
            $fileSystem,
            $utilHelper,
            $fileUtilsHelper,
            $legacyPathHelper,
            'Some Root Directory',
            'someclient',
            $fileSystem
        );

        $upload->uploadBackgroundImage('some/where/temporary', '3');
    }

    /**
     * This test ensures the workaround for https://mantis.ilias.de/view.php?id=28219 works as expected
     */
    public function testPendingFilesCanBeUploaded() : void
    {
        $fileUpload = $this->getMockBuilder(FileUpload::class)
            ->getMock();

        $fileUpload->method('hasBeenProcessed')
            ->willReturn(false);

        $fileUpload->expects($this->once())
            ->method('process');

        $fileUpload->method('hasUploads')
            ->willReturn(true);

        $uploadResult = new UploadResult(
            '',
            0,
            '',
            $this->getMockBuilder(ImmutableStringMap::class)->getMock(),
            new ProcessingStatus(ProcessingStatus::REJECTED, 'rejected'),
            ''
        );

        $fileUpload->method('getResults')
            ->willReturn([0 => $uploadResult]);

        $fileUpload->expects($this->never())
            ->method('moveOneFileTo');

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileSystem = $this->getMockBuilder(Filesystem::class)
            ->getMock();

        $fileSystem->method('hasDir')
            ->willReturn(true);

        $fileSystem->method('has')
            ->willReturn(true);

        $fileSystem->expects($this->once())
            ->method('delete');

        $fileSystem->expects($this->once())
            ->method('writeStream');

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $utilHelper->expects($this->exactly(2))
            ->method('convertImage');

        $fileUtilsHelper = $this->getMockBuilder(ilCertificateFileUtilsHelper::class)
            ->getMock();

        $legacyPathHelper = $this->getMockBuilder(LegacyPathHelperHelper::class)
            ->getMock();

        $legacyPathHelper->method('createRelativePath')
            ->willReturn('the/relative/path');

        $tmp_file_system = $this->getMockBuilder(Filesystem::class)
            ->getMock();
        $tmp_file_system->expects($this->once())
            ->method('readStream')
            ->willReturn($this->getMockBuilder(FileStream::class)->getMock());

        $upload = new ilCertificateBackgroundImageUpload(
            $fileUpload,
            'certifcate/path/to/some/where',
            $language,
            $logger,
            $fileSystem,
            $utilHelper,
            $fileUtilsHelper,
            $legacyPathHelper,
            'Some Root Directory',
            'someclient',
            $tmp_file_system
        );

        $upload->uploadBackgroundImage('some/where/temporary', '3', [
            'tmp_name' => 'pending_file'
        ]);
    }
}
