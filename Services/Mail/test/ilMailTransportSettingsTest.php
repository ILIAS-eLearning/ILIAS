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
            ->onlyMethods(['updateOptions'])
            ->getMock();

        $mailOptions->setIncomingType(0);
        $mailOptions->setEmailAddressMode(3);

        $setting = new ilMailTransportSettings($mailOptions);
        $setting->adjust('test@ilias-test.de', 'someone@php-test.net');

        $this->assertSame(0, $mailOptions->getIncomingType());
        $this->assertSame(3, $mailOptions->getEmailAddressMode());
    }

    /**
     * @throws ReflectionException
     */
    public function testOnlyFirstMailWillResultInUpdateProcess() : void
    {
        $mailOptions = $this->getMockBuilder(ilMailOptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateOptions'])
            ->getMock();

        $mailOptions->expects($this->once())->method('updateOptions');
        $mailOptions->setIncomingType(2);
        $mailOptions->setEmailAddressMode(4);

        $setting = new ilMailTransportSettings($mailOptions);
        $setting->adjust('test@ilias-test.de', '');

        $this->assertSame(3, $mailOptions->getEmailAddressMode());
    }

    /**
     * @throws ReflectionException
     */
    public function testOnlySecondMailWillResultInUpdateProcess() : void
    {
        $mailOptions = $this->getMockBuilder(ilMailOptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateOptions'])
            ->getMock();

        $mailOptions->expects($this->once())->method('updateOptions');
        $mailOptions->setIncomingType(2);
        $mailOptions->setEmailAddressMode(3);

        $setting = new ilMailTransportSettings($mailOptions);
        $setting->adjust('', 'test@ilias-test.de');

        $this->assertSame(4, $mailOptions->getEmailAddressMode());
    }

    /**
     * @throws ReflectionException
     */
    public function testNoMailWillResultInUpdateProcess() : void
    {
        $mailOptions = $this->getMockBuilder(ilMailOptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateOptions'])
            ->getMock();

        $mailOptions->expects($this->once())->method('updateOptions');
        $mailOptions->setIncomingType(2);
        $mailOptions->setEmailAddressMode(3);

        $setting = new ilMailTransportSettings($mailOptions);
        $setting->adjust('', '');

        $this->assertSame(0, $mailOptions->getIncomingType());
    }

    /**
     * @throws ReflectionException
     */
    public function testNothingWillBeAdjusted() : void
    {
        $mailOptions = $this->getMockBuilder(ilMailOptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateOptions'])
            ->getMock();

        $mailOptions->expects($this->never())->method('updateOptions');
        $mailOptions->setIncomingType(2);
        $mailOptions->setEmailAddressMode(5);

        $setting = new ilMailTransportSettings($mailOptions);
        $setting->adjust('test@ilias-test.de', 'someone@php-test.net');

        $this->assertSame(2, $mailOptions->getIncomingType());
        $this->assertSame(5, $mailOptions->getEmailAddressMode());
    }
}
