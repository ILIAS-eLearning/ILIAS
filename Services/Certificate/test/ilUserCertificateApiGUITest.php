<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateApiGUITest extends ilCertificateBaseTestCase
{
    /**
     * 
     */
    public function testCreationOfGuiClass() : void
    {
        $language = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();

        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder('ilCtrl')
            ->disableOriginalConstructor()
            ->getMock();

        $gui = new ilUserCertificateApiGUI($language, $request, $logger, $controller);
        $this->assertInstanceOf(ilUserCertificateApiGUI::class, $gui);
    }
}
