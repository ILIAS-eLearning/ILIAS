<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateBackgroundImageUploadTest extends PHPUnit_Framework_TestCase
{
    public function testFileCanBeUploaded()
    {
        $fileUpload = $this->getMockBuilder('\ILIAS\FileUpload\FileUpload')
            ->disableOriginalConstructor()
            ->getMock();

        $fileUpload->method('hasBeenProcessed')
            ->willReturn(false);

        $fileUpload->expects($this->once())
            ->method('process');

        $fileUpload->method('hasUploads')
            ->willReturn(true);

        $uploadResult = new \ILIAS\FileUpload\DTO\UploadResult(
            'phpunit',
            1024,
            'text/xml',
            $this->getMockBuilder(\ILIAS\FileUpload\Collection\ImmutableStringMap::class)->getMock(),
            new \ILIAS\FileUpload\DTO\ProcessingStatus(\ILIAS\FileUpload\DTO\ProcessingStatus::OK, 'uploaded'),
            '/tmp'
        );

        $fileUpload->method('getResults')
            ->willReturn(array("some/where/temporary" => $uploadResult));

        $fileUpload->expects($this->once())
            ->method('moveOneFileTo');

        $language = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $fileSystem = $this->getMockBuilder('\ILIAS\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $fileSystem->method('hasDir')
            ->willReturn(true);

        $fileSystem->method('has')
            ->willReturn(true);

        $fileSystem->expects($this->once())
            ->method('delete');

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

        $utilHelper->expects($this->exactly(2))
            ->method('convertImage');

        $fileUtilsHelper = $this->getMockBuilder('ilCertificateFileUtilsHelper')
            ->getMock();

        $legacyPathHelper = $this->getMockBuilder('LegacyPathHelperHelper')
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
            'someclient'
        );

        $upload->uploadBackgroundImage('some/where/temporary', '3');
    }
}
