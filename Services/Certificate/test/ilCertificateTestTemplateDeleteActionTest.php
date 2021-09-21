<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTestTemplateDeleteActionTest extends ilCertificateBaseTestCase
{
    public function testDelete()
    {
        $deleteAction = $this->getMockBuilder('ilCertificateDeleteAction')
            ->getMock();

        $deleteAction
            ->expects($this->once())
            ->method('delete');

        $objectHelper = $this->getMockBuilder('ilCertificateObjectHelper')
            ->getMock();

        $object = $this->getMockBuilder('ilObjTest')
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper->method('getInstanceByObjId')
            ->willReturn($object);

        $action = new ilCertificateTestTemplateDeleteAction(
            $deleteAction,
            $objectHelper
        );

        $action->delete(100, 200, 'v5.4.0');
    }
}
