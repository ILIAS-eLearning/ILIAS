<?php declare(strict_types=1);

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
class ilCertificateScormTemplateDeleteActionTest extends ilCertificateBaseTestCase
{
    public function testDeleteScormTemplateAndSettings() : void
    {
        $deleteMock = $this->getMockBuilder(ilCertificateTemplateDeleteAction::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['delete'])
            ->getMock();

        $deleteMock->expects($this->once())
            ->method('delete');

        $settingMock = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $action = new ilCertificateScormTemplateDeleteAction($deleteMock, $settingMock);

        $action->delete(10, 200);
    }
}
