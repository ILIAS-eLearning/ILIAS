<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTransportSettingsTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailTransportSettingsTest extends ilMailBaseTest
{
    /**
     * @throws ReflectionException
     */
    public function testSystemAsIncomingTypeWontUpdate() : void
    {
        $mailOptions = $this->getMockBuilder(ilMailOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateOptions'])
            ->getMock();

        $mailOptions->setIncomingType(0);
        $mailOptions->setMailAddressOption(3);

        $setting = new ilMailTransportSettings($mailOptions);
        $setting->adjust('test@ilias-test.de', 'someone@php-test.net');

        $this->assertEquals(0, $mailOptions->getIncomingType());
        $this->assertEquals(3, $mailOptions->getMailAddressOption());
    }

    /**
     * @throws ReflectionException
     */
    public function testOnlyFirstMailWillResultInUpdateProcess() : void
    {
        $mailOptions = $this->getMockBuilder(ilMailOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateOptions'])
            ->getMock();

        $mailOptions->expects($this->once())->method('updateOptions');
        $mailOptions->setIncomingType(2);
        $mailOptions->setMailAddressOption(4);

        $setting = new ilMailTransportSettings($mailOptions);
        $setting->adjust('test@ilias-test.de', '');

        $this->assertEquals(3, $mailOptions->getMailAddressOption());
    }

    /**
     * @throws ReflectionException
     */
    public function testOnlySecondMailWillResultInUpdateProcess() : void
    {
        $mailOptions = $this->getMockBuilder(ilMailOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateOptions'])
            ->getMock();

        $mailOptions->expects($this->once())->method('updateOptions');
        $mailOptions->setIncomingType(2);
        $mailOptions->setMailAddressOption(3);

        $setting = new ilMailTransportSettings($mailOptions);
        $setting->adjust('', 'test@ilias-test.de');

        $this->assertEquals(4, $mailOptions->getMailAddressOption());
    }

    /**
     * @throws ReflectionException
     */
    public function testNoMailWillResultInUpdateProcess() : void
    {
        $mailOptions = $this->getMockBuilder(ilMailOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateOptions'])
            ->getMock();

        $mailOptions->expects($this->once())->method('updateOptions');
        $mailOptions->setIncomingType(2);
        $mailOptions->setMailAddressOption(3);

        $setting = new ilMailTransportSettings($mailOptions);
        $setting->adjust('', '');

        $this->assertEquals(0, $mailOptions->getIncomingType());
    }

    /**
     * @throws ReflectionException
     */
    public function testNothingWillBeAdjusted() : void
    {
        $mailOptions = $this->getMockBuilder(ilMailOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateOptions'])
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
