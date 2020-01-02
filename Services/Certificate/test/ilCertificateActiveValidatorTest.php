<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateActiveValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testCertificatesAreActiveAndJavaServerIsActive()
    {
        $settings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->with('active')
            ->willReturn(true);

        $rpcSettings = $this->getMockBuilder('ilRPCServerSettings')
            ->disableOriginalConstructor()
            ->getMock();

        $rpcSettings->method('isEnabled')
            ->willReturn(true);

        $validator = new ilCertificateActiveValidator($settings, $rpcSettings);

        $result = $validator->validate();

        $this->assertTrue($result);
    }

    public function testValidationReturnFalseBecauseGlobalCertificatesAreInactive()
    {
        $settings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->with('active')
            ->willReturn(false);

        $rpcSettings = $this->getMockBuilder('ilRPCServerSettings')
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

    public function testValidationReturnFalseBecauseJavaServerIsInactive()
    {
        $settings = $this->getMockBuilder('ilSetting')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('get')
            ->with('active')
            ->willReturn(true);

        $rpcSettings = $this->getMockBuilder('ilRPCServerSettings')
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
