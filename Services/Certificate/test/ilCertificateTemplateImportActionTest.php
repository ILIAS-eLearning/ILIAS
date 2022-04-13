<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateImportActionTest extends ilCertificateBaseTestCase
{
    public function testCertificateCanBeImportedWithBackgroundImage() : void
    {
        $placeholderDescriptionObject = $this->getMockBuilder(ilCertificatePlaceholderDescription::class)
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem = $this->getMockBuilder(ILIAS\Filesystem\Filesystem::class)
            ->getMock();

        $filesystem
            ->expects($this->never())
            ->method('deleteDir');

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $objectHelper->method('lookupType')
            ->willReturn('crs');

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $utilHelper
            ->method('moveUploadedFile')
            ->willReturn(true);

        $utilHelper
            ->expects($this->once())
            ->method('unzip');

        $utilHelper
            ->method('getDir')
            ->willReturn(array(
                array(
                    'type' => 'file',
                    'entry' => 'background.jpg'
                ),
                array(
                    'type' => 'file',
                    'entry' => 'certificate.xml'
                )
            ));

        $utilHelper
            ->expects($this->once())
            ->method('convertImage');

        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $action = new ilCertificateTemplateImportAction(
            100,
            'some/path/certiicate.xml',
            $placeholderDescriptionObject,
            $logger,
            $filesystem,
            $templateRepository,
            $objectHelper,
            $utilHelper,
            $database,
            new ilCertificateBackgroundImageFileService('/some/path', $filesystem, '/some/web/dir')
        );

        $result = $action->import(
            'someZipFile.zip',
            'some/path/',
            'some/root/path',
            'v5.4.0',
            'someInstallationId'
        );

        $this->assertSame(true, $result);
    }

    public function testCertificateCanBeImportedWithoutBackgroundImage() : void
    {
        $placeholderDescriptionObject = $this->getMockBuilder(ilCertificatePlaceholderDescription::class)
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem = $this->getMockBuilder(ILIAS\Filesystem\Filesystem::class)
            ->getMock();

        $filesystem
            ->expects($this->never())
            ->method('deleteDir');

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $objectHelper->method('lookupType')
            ->willReturn('crs');

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $utilHelper
            ->method('moveUploadedFile')
            ->willReturn(true);

        $utilHelper
            ->expects($this->once())
            ->method('unzip');

        $utilHelper
            ->method('getDir')
            ->willReturn(array(
                array(
                    'type' => 'file',
                    'entry' => 'certificate.xml'
                )
            ));

        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $action = new ilCertificateTemplateImportAction(
            100,
            'some/path/certiicate.xml',
            $placeholderDescriptionObject,
            $logger,
            $filesystem,
            $templateRepository,
            $objectHelper,
            $utilHelper,
            $database,
            new ilCertificateBackgroundImageFileService('/some/path', $filesystem, '/some/web/dir')
        );

        $result = $action->import(
            'someZipFile.zip',
            'some/path/',
            'some/root/path',
            'v5.4.0',
            'someInstallationId'
        );

        $this->assertSame(true, $result);
    }

    public function testNoXmlFileInUplodadZipFolder() : void
    {
        $placeholderDescriptionObject = $this->getMockBuilder(ilCertificatePlaceholderDescription::class)
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem = $this->getMockBuilder(ILIAS\Filesystem\Filesystem::class)
            ->getMock();

        $filesystem
            ->expects($this->once())
            ->method('deleteDir');

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $utilHelper
            ->method('moveUploadedFile')
            ->willReturn(true);

        $utilHelper
            ->expects($this->once())
            ->method('unzip');

        $utilHelper
            ->method('getDir')
            ->willReturn(array());

        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $action = new ilCertificateTemplateImportAction(
            100,
            'some/path/certiicate.xml',
            $placeholderDescriptionObject,
            $logger,
            $filesystem,
            $templateRepository,
            $objectHelper,
            $utilHelper,
            $database,
            new ilCertificateBackgroundImageFileService('/some/path', $filesystem, '/some/web/dir')
        );

        $result = $action->import(
            'someZipFile.zip',
            'some/path/',
            'some/root/path',
            'v5.4.0',
            'someInstallationId'
        );

        $this->assertSame(false, $result);
    }

    public function testZipfileCouldNoBeMoved() : void
    {
        $placeholderDescriptionObject = $this->getMockBuilder(ilCertificatePlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(ilLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem = $this->getMockBuilder(ILIAS\Filesystem\Filesystem::class)
            ->getMock();

        $filesystem
            ->expects($this->once())
            ->method('deleteDir');

        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->getMock();

        $utilHelper
            ->method('moveUploadedFile')
            ->willReturn(false);

        $database = $this->getMockBuilder(ilDBInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $action = new ilCertificateTemplateImportAction(
            100,
            'some/path/certiicate.xml',
            $placeholderDescriptionObject,
            $logger,
            $filesystem,
            $templateRepository,
            $objectHelper,
            $utilHelper,
            $database,
            new ilCertificateBackgroundImageFileService('/some/path', $filesystem, '/some/web/dir')
        );

        $result = $action->import(
            'someZipFile.zip',
            'some/path/',
            'some/root/path',
            'v5.4.0',
            'someInstallationId'
        );

        $this->assertSame(false, $result);
    }
}
