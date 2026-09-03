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

class ilCertificateTemplateDeleteActionTest extends ilCertificateBaseTestCase
{
    public function testDeleteTemplateAndUseOldThumbnail(): void
    {
        $templateRepositoryMock = $this->getMockBuilder(ilCertificateTemplateRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $templateRepositoryMock
            ->expects($this->once())
            ->method('deleteTemplate')
            ->with(100, 2000);

        $templateRepositoryMock->expects($this->once())->method('save');

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->getMock();

        $objectHelper->method('lookUpType')
            ->willReturn('crs');

        $action = new ilCertificateTemplateDeleteAction(
            $templateRepositoryMock,
            'v5.4.0',
            $objectHelper
        );

        $action->delete(100, 2000);
    }
}
