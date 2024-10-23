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

use ILIAS\ResourceStorage\Services as IRSS;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTemplateExportActionTest extends ilCertificateBaseTestCase
{
    public function testExport(): void
    {
        $templateRepository = $this->getMockBuilder(ilCertificateTemplateRepository::class)->getMock();

        $templateRepository->method('fetchCurrentlyActiveCertificate')
            ->willReturn(new ilCertificateTemplate(
                100,
                'crs',
                '<xml> Some Content </xml>',
                md5('<xml> Some Content </xml>'),
                '[]',
                3,
                'v5.4.0',
                123_456_789,
                true,
                '/some/where/background.jpg',
                '/some/where/thumbnail.jpg',
                '-',
                '-',
                50
            ));

        $irss = $this->getMockBuilder(IRSS::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $objectHelper->method('lookupType')
            ->willReturn('crs');

        $utilHelper = $this->getMockBuilder(ilCertificateUtilHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $utilHelper
            ->expects($this->once())
            ->method('zipAndDeliver');

        $action = new ilCertificateTemplateExportAction(
            100,
            '/some/where/background.jpg',
            $templateRepository,
            $irss,
            $objectHelper,
            $utilHelper
        );

        $action->export('some/where/root', 'phpunit');
    }
}
