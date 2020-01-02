<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateImportActionTest extends PHPUnit_Framework_TestCase
{
    public function testCertificateCanBeImportedWithBackgroundImage()
    {
        $placeholderDescriptionObject = $this->getMockBuilder('ilCertificatePlaceholderDescription')
            ->getMock();

        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem = $this->getMockBuilder('ILIAS\Filesystem\Filesystem')
            ->getMock();

        $filesystem
            ->expects($this->never())
            ->method('deleteDir');

        $templateRepository = $this->getMockBuilder('ilCertificateTemplateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->getMock();

        $objectHelper->method('lookupType')
            ->willReturn('crs');

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
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

        $database = $this->getMockBuilder('ilDBInterface')
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
            'someInstallationId'
        );

        $result = $action->import(
            'someZipFile.zip',
            'some/path/',
            'some/root/path',
            'v5.4.0',
            'someInstallationId'
        );

        $this->assertEquals(true, $result);
    }

    public function testCertificateCanBeImportedWithoutBackgroundImage()
    {
        $placeholderDescriptionObject = $this->getMockBuilder('ilCertificatePlaceholderDescription')
            ->getMock();

        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem = $this->getMockBuilder('ILIAS\Filesystem\Filesystem')
            ->getMock();

        $filesystem
            ->expects($this->never())
            ->method('deleteDir');

        $templateRepository = $this->getMockBuilder('ilCertificateTemplateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->getMock();

        $objectHelper->method('lookupType')
            ->willReturn('crs');

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
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

        $database = $this->getMockBuilder('ilDBInterface')
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
            'someInstallationId'
        );

        $result = $action->import(
            'someZipFile.zip',
            'some/path/',
            'some/root/path',
            'v5.4.0',
            'someInstallationId'
        );

        $this->assertEquals(true, $result);
    }

    public function testNoXmlFileInUplodadZipFolder()
    {
        $placeholderDescriptionObject = $this->getMockBuilder('ilCertificatePlaceholderDescription')
            ->getMock();

        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem = $this->getMockBuilder('ILIAS\Filesystem\Filesystem')
            ->getMock();

        $filesystem
            ->expects($this->once())
            ->method('deleteDir');

        $templateRepository = $this->getMockBuilder('ilCertificateTemplateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->getMock();

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
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

        $database = $this->getMockBuilder('ilDBInterface')
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
            $database
        );

        $result = $action->import(
            'someZipFile.zip',
            'some/path/',
            'some/root/path',
            'v5.4.0',
            'someInstallationId'
        );

        $this->assertEquals(false, $result);
    }

    public function testZipfileCouldNoBeMoved()
    {
        $placeholderDescriptionObject = $this->getMockBuilder('ilCertificatePlaceholderDescription')
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $filesystem = $this->getMockBuilder('ILIAS\Filesystem\Filesystem')
            ->getMock();

        $filesystem
            ->expects($this->once())
            ->method('deleteDir');

        $templateRepository = $this->getMockBuilder('ilCertificateTemplateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->getMock();

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

        $utilHelper
            ->method('moveUploadedFile')
            ->willReturn(false);

        $database = $this->getMockBuilder('ilDBInterface')
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
            'someInstallationId'
        );

        $result = $action->import(
            'someZipFile.zip',
            'some/path/',
            'some/root/path',
            'v5.4.0',
            'someInstallationId'
        );

        $this->assertEquals(false, $result);
    }
}
