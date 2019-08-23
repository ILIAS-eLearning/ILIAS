<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificateApiGUITest extends ilCertificateBaseTestCase
{
    public function testCreationOfGuiClass()
    {
        $language = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $request = new \GuzzleHttp\Psr7\Request('GET', 'ilias.de');

        $logger = $this->getMockBuilder('ilLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $gui = new ilUserCertificateApiGUI($language, $request, $logger);
    }
}
