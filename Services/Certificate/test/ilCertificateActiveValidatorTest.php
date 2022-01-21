<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateActiveValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testCertificatesAreActiveAndJavaServerIsActive() : void
    {
        $settings = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->with('active')
            ->willReturn('1');

        $rpcSettings = $this->getMockBuilder(ilRPCServerSettings::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rpcSettings->method('isEnabled')
            ->willReturn(true);

        $validator = new ilCertificateActiveValidator($settings, $rpcSettings);

        $result = $validator->validate();

        $this->assertTrue($result);
    }

    public function testValidationReturnFalseBecauseGlobalCertificatesAreInactive() : void
    {
        $settings = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->with('active')
            ->willReturn('0');

        $rpcSettings = $this->getMockBuilder(ilRPCServerSettings::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rpcSettings
            ->expects($this->never())
            ->method('isEnabled')
            ->willReturn(true);

        $validator = new ilCertificateActiveValidator($settings, $rpcSettings);

        $result = $validator->validate();

        $this->assertFalse($result);
    }

    public function testValidationReturnFalseBecauseJavaServerIsInactive() : void
    {
        $settings = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->with('active')
            ->willReturn('1');

        $rpcSettings = $this->getMockBuilder(ilRPCServerSettings::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rpcSettings
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $validator = new ilCertificateActiveValidator($settings, $rpcSettings);

        $result = $validator->validate();

        $this->assertFalse($result);
    }
}
