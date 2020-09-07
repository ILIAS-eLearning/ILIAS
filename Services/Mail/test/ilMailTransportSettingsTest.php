<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTransportSettingsTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailTransportSettingsTest extends \ilMailBaseTest
{
    /**
     *
     */
    public function testSystemAsIncomingTypeWontUpdate()
    {
        $mailOptions = $this->getMockBuilder(\ilMailOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(array('updateOptions'))
            ->getMock();

        $mailOptions->setIncomingType(0);
        $mailOptions->setMailAddressOption(3);

        $setting = new ilMailTransportSettings($mailOptions);
        $setting->adjust('test@ilias-test.de', 'someone@php-test.net');


        $this->assertEquals(0, $mailOptions->getIncomingType());
        $this->assertEquals(3, $mailOptions->getMailAddressOption());
    }

    /**
     *
     */
    public function testOnlyFirstMailWillResultInUpdateProcess()
    {
        $mailOptions = $this->getMockBuilder(\ilMailOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(array('updateOptions'))
            ->getMock();

        $mailOptions->expects($this->once())->method('updateOptions');
        $mailOptions->setIncomingType(2);
        $mailOptions->setMailAddressOption(4);

        $setting = new ilMailTransportSettings($mailOptions);
        $setting->adjust('test@ilias-test.de', '');


        $this->assertEquals(3, $mailOptions->getMailAddressOption());
    }

    /**
     *
     */
    public function testOnlySecondMailWillResultInUpdateProcess()
    {
        $mailOptions = $this->getMockBuilder(\ilMailOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(array('updateOptions'))
            ->getMock();

        $mailOptions->expects($this->once())->method('updateOptions');
        $mailOptions->setIncomingType(2);
        $mailOptions->setMailAddressOption(3);

        $setting = new ilMailTransportSettings($mailOptions);
        $setting->adjust('', 'test@ilias-test.de');


        $this->assertEquals(4, $mailOptions->getMailAddressOption());
    }

    /**
     *
     */
    public function testNoMailWillResultInUpdateProcess()
    {
        $mailOptions = $this->getMockBuilder(\ilMailOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(array('updateOptions'))
            ->getMock();

        $mailOptions->expects($this->once())->method('updateOptions');
        $mailOptions->setIncomingType(2);
        $mailOptions->setMailAddressOption(3);

        $setting = new ilMailTransportSettings($mailOptions);
        $setting->adjust('', '');

        $this->assertEquals(0, $mailOptions->getIncomingType());
    }

    /**
     *
     */
    public function testNothingWillBeAdjusted()
    {
        $mailOptions = $this->getMockBuilder(\ilMailOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(array('updateOptions'))
            ->getMock();

        $mailOptions->expects($this->never())->method('updateOptions');
        $mailOptions->setIncomingType(2);
        $mailOptions->setMailAddressOption(5);

        $setting = new ilMailTransportSettings($mailOptions);
        $setting->adjust('test@ilias-test.de', 'someone@php-test.net');

        $this->assertEquals(2, $mailOptions->getIncomingType());
        $this->assertEquals(5, $mailOptions->getMailAddressOption());
    }
}
