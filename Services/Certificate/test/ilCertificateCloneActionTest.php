<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateCloneActionTest extends ilCertificateBaseTestCase
{
    public function testCloneCertificate()
    {
        $database = $this->getMockBuilder('ilDBInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $database
            ->expects($this->once())
            ->method('query');

        $database
            ->expects($this->once())
            ->method('numRows')
            ->willReturn(1);

        $database
            ->expects($this->once())
            ->method('replace');



        $templateRepository = $this->getMockBuilder('ilCertificateTemplateRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $templateRepository->method('fetchCertificateTemplatesByObjId')
            ->willReturn(
                array(
                    new ilCertificateTemplate(
                        10,
                        'crs',
                        '<xml> Some Content </xml>',
                        md5('<xml> Some Content </xml>'),
                        '[]',
                        '3',
                        'v5.3.0',
                        123456789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/card_thumb.jpg',
                        $id = null
                    ),
                    new ilCertificateTemplate(
                        20,
                        'crs',
                        '<xml> Some Content </xml>',
                        md5('<xml> Some Content </xml>'),
                        '[]',
                        '3',
                        'v5.3.0',
                        123456789,
                        true,
                        '/some/where/background.jpg',
                        '/some/where/card_thumb.jpg',
                        $id = null
                    ),
                    new ilCertificateTemplate(
                        30,
                        'crs',
                        '<xml> Some Content </xml>',
                        md5('<xml> Some Content </xml>'),
                        '[]',
                        '3',
                        'v5.3.0',
                        123456789,
                        true,
                        '/certificates/default/background.jpg',
                        '/some/where/card_thumb.jpg',
                        $id = null
                    )
                )
            );

        $templateRepository
            ->expects($this->exactly(3))
            ->method('save');

        $fileSystem = $this->getMockBuilder('\ILIAS\Filesystem\Filesystem')
            ->getMock();

        $fileSystem->method('has')
            ->willReturn(true);

        $fileSystem
            ->expects($this->exactly(7))
            ->method('copy');

        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->getMock();

        $objectHelper->method('lookupObjId')
            ->willReturn(1000);

        $global_certificate_settings = $this->getMockBuilder(ilObjCertificateSettings::class)
            ->disableOriginalConstructor()
            ->getMock();


        $cloneAction = new ilCertificateCloneAction(
            $database,
            new ilCertificatePathFactory(),
            $templateRepository,
            $fileSystem,
            $logger,
            $objectHelper,
            $global_certificate_settings,
            'some/web/directory',
            '/certificates/default/background.jpg'
        );

        $oldObject = $this->getMockBuilder('ilObject')
            ->disableOriginalConstructor()
            ->getMock();

        $oldObject->method('getType')
            ->willReturn('crs');

        $oldObject->method('getId')
            ->willReturn(10);

        $newObject = $this->getMockBuilder('ilObject')
            ->disableOriginalConstructor()
            ->getMock();

        $newObject->method('getType')
            ->willReturn('crs');

        $newObject->method('getId')
            ->willReturn(10);

        $cloneAction->cloneCertificate($oldObject, $newObject, 'v5.4.0', '/some/web/dir');
    }
}
