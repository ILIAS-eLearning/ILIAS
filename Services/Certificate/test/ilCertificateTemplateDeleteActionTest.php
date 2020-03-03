<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateDeleteActionTest extends \PHPUnit_Framework_TestCase
{
    public function testDeleteTemplateAndUseOldThumbnail()
    {
        $templateRepositoryMock = $this->getMockBuilder('ilCertificateTemplateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $templateRepositoryMock
            ->method('deleteTemplate')
            ->with(100, 2000);

        $templateRepositoryMock->method('activatePreviousCertificate')
            ->with(2000)
            ->willReturn(new ilCertificateTemplate(
                2000,
                'crs',
                'something',
                md5('something'),
                '[]',
                '1',
                'v5.4.0',
                1234567890,
                true,
                'samples/background.jpg'
            ));

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

        $utilHelper
            ->expects($this->once())
            ->method('convertImage');

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->getMock();

        $objectHelper->method('lookUpType')
            ->willReturn('crs');

        $action = new ilCertificateTemplateDeleteAction(
            $templateRepositoryMock,
            __DIR__,
            $utilHelper,
            $objectHelper,
            'v5.4.0'
        );

        $action->delete(100, 2000);
    }

    public function testDeleteTemplateButNoThumbnailWillBeCopiedFromOldCertificate()
    {
        $templateRepositoryMock = $this->getMockBuilder('ilCertificateTemplateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $templateRepositoryMock
            ->method('deleteTemplate')
            ->with(100, 2000);

        $templateRepositoryMock->method('activatePreviousCertificate')
            ->with(2000)
            ->willReturn(new ilCertificateTemplate(
                2000,
                'crs',
                'something',
                md5('something'),
                '[]',
                '1',
                'v5.4.0',
                1234567890,
                true
            ));

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

        $utilHelper
            ->expects($this->once())
            ->method('convertImage');

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->getMock();

        $objectHelper->method('lookUpType')
            ->willReturn('crs');

        $action = new ilCertificateTemplateDeleteAction(
            $templateRepositoryMock,
            __DIR__,
            $utilHelper,
            $objectHelper,
            'v5.4.0'
        );

        $action->delete(100, 2000);
    }
}
