<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

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

        $action->delete(10, 200, 'v5.4.0');
    }
}
