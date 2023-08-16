<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateDeleteActionTest extends ilCertificateBaseTestCase
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

        $templateRepositoryMock->expects($this->once())->method("deleteTemplate");
        $templateRepositoryMock->expects($this->once())->method("save");

        $utilHelper = $this->getMockBuilder('ilCertificateUtilHelper')
            ->getMock();

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
