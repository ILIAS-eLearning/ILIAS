<?php

declare(strict_types=1);

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

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateImportActionTest extends ilCertificateBaseTestCase
{
    public function testCertificateCanBeImportedWithBackgroundImage(): void
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
            ->willReturn([
                [
                    'type' => 'file',
                    'entry' => 'background.jpg'
                ],
                [
                    'type' => 'file',
                    'entry' => 'certificate.xml'
                ]
            ]);

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

        $this->assertTrue($result);
    }

    public function testCertificateCanBeImportedWithoutBackgroundImage(): void
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
            ->willReturn([
                [
                    'type' => 'file',
                    'entry' => 'certificate.xml'
                ]
            ]);

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

        $this->assertTrue($result);
    }

    public function testNoXmlFileInUplodadZipFolder(): void
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
            ->willReturn([]);

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

        $this->assertFalse($result);
    }

    public function testZipfileCouldNoBeMoved(): void
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

        $this->assertFalse($result);
    }
}
