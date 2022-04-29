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
