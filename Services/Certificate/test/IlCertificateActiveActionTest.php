<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class IlCertificateActiveActionTest extends \PHPUnit_Framework_TestCase
{
    public function testCertificateIsActive()
    {
        $databaseMock = $this->getMockBuilder('ilDBInterface')
            ->getMock();

        $databaseMock->expects($this->atLeastOnce())
            ->method('query');

        $databaseMock->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->willReturn(array(1, 2, 3));

        $activateAction = new ilCertificateActiveAction($databaseMock);
        $result = $activateAction->isObjectActive(10);

        $this->assertTrue($result);
    }

    public function testCertificateIsNotActive()
    {
        $databaseMock = $this->getMockBuilder('ilDBInterface')
            ->getMock();


        $databaseMock->expects($this->atLeastOnce())
            ->method('query');

        $databaseMock->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->willReturn(array());

        $activateAction = new ilCertificateActiveAction($databaseMock);
        $result = $activateAction->isObjectActive(10);

        $this->assertFalse($result);
    }
}
