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

use PHPUnit\Framework\MockObject\Stub;

class ilCertificateCloneActionTest extends ilCertificateBaseTestCase
{
    public function testCloneCertificate(): void
    {
        $database = $this->createMock(ilDBInterface::class);

        $database
            ->expects($this->once())
            ->method('query');

        $database
            ->expects($this->once())
            ->method('fetchAssoc')
            ->willReturn(['1' => '1']);

        $database
            ->expects($this->once())
            ->method('replace');



        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $templateRepository->method('fetchCertificateTemplatesByObjId')
            ->willReturn(
                [
                    new ilCertificateTemplate(
                        10,
                        'crs',
                        '<xml> Some Content </xml>',
                        md5('<xml> Some Content </xml>'),
                        '[]',
                        3,
                        'v5.3.0',
                        123_456_789,
                        true,
                        '-',
                        '-',
                        null
                    ),
                    new ilCertificateTemplate(
                        20,
                        'crs',
                        '<xml> Some Content </xml>',
                        md5('<xml> Some Content </xml>'),
                        '[]',
                        3,
                        'v5.3.0',
                        123_456_789,
                        true,
                        '-',
                        '-',
                        null
                    ),
                    new ilCertificateTemplate(
                        30,
                        'crs',
                        '<xml> Some Content </xml>',
                        md5('<xml> Some Content </xml>'),
                        '[]',
                        3,
                        'v5.3.0',
                        123_456_789,
                        true,
                        '-',
                        '-',
                        null
                    )
                ]
            );

        $templateRepository
            ->expects($this->exactly(3))
            ->method('save');

        /** @var ilCertificateObjectHelper&Stub $objectHelper */
        $objectHelper = $this->createStub(ilCertificateObjectHelper::class);

        $objectHelper->method('lookupObjId')
            ->willReturn(1000);
        $objectHelper->method('lookupType')
            ->willReturn('crs');

        /** @var ilObjCertificateSettings&Stub $global_certificate_settings */
        $global_certificate_settings = $this->createStub(ilObjCertificateSettings::class);

        $cloneAction = new ilCertificateCloneAction(
            $database,
            new ilCertificatePathFactory(),
            $templateRepository,
            $objectHelper,
            $global_certificate_settings
        );

        /** @var ilObject&Stub $oldObject */
        $oldObject = $this->createStub(ilObject::class);

        $oldObject->method('getType')
            ->willReturn('crs');

        $oldObject->method('getId')
            ->willReturn(10);

        /** @var ilObject&Stub $newObject */
        $newObject = $this->createStub(ilObject::class);

        $newObject->method('getType')
            ->willReturn('crs');

        $newObject->method('getId')
            ->willReturn(10);

        $cloneAction->cloneCertificate($oldObject, $newObject, 'v5.4.0', '/some/web/dir');
    }
}
