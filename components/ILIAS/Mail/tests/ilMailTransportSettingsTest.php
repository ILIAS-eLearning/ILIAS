<?php

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

declare(strict_types=1);

class ilMailTransportSettingsTest extends ilMailBaseTestCase
{
    public function testSystemAsIncomingTypeWontUpdate(): void
    {
        $mail_options = $this->getMockBuilder(ilMailOptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateOptions'])
            ->getMock();

        $mail_options->setIncomingType(0);
        $mail_options->setEmailAddressmode(3);

        $setting = new ilMailTransportSettings($mail_options);
        $setting->adjust('test@ilias-test.de', 'someone@php-test.net');

        $this->assertSame(0, $mail_options->getIncomingType());
        $this->assertSame(3, $mail_options->getEmailAddressMode());
    }

    public function testOnlyFirstMailWillResultInUpdateProcess(): void
    {
        $mail_options = $this->getMockBuilder(ilMailOptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateOptions'])
            ->getMock();

        $mail_options->expects($this->once())->method('updateOptions');
        $mail_options->setIncomingType(2);
        $mail_options->setEmailAddressmode(4);

        $setting = new ilMailTransportSettings($mail_options);
        $setting->adjust('test@ilias-test.de', '');

        $this->assertSame(3, $mail_options->getEmailAddressMode());
    }

    public function testOnlySecondMailWillResultInUpdateProcess(): void
    {
        $mail_options = $this->getMockBuilder(ilMailOptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateOptions'])
            ->getMock();

        $mail_options->expects($this->once())->method('updateOptions');
        $mail_options->setIncomingType(2);
        $mail_options->setEmailAddressmode(3);

        $setting = new ilMailTransportSettings($mail_options);
        $setting->adjust('', 'test@ilias-test.de');

        $this->assertSame(4, $mail_options->getEmailAddressMode());
    }

    public function testNoMailWillResultInUpdateProcess(): void
    {
        $mail_options = $this->getMockBuilder(ilMailOptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateOptions'])
            ->getMock();

        $mail_options->expects($this->once())->method('updateOptions');
        $mail_options->setIncomingType(2);
        $mail_options->setEmailAddressmode(3);

        $setting = new ilMailTransportSettings($mail_options);
        $setting->adjust('', '');

        $this->assertSame(0, $mail_options->getIncomingType());
    }

    public function testNothingWillBeAdjusted(): void
    {
        $mail_options = $this->getMockBuilder(ilMailOptions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateOptions'])
            ->getMock();

        $mail_options->expects($this->never())->method('updateOptions');
        $mail_options->setIncomingType(2);
        $mail_options->setEmailAddressmode(5);

        $setting = new ilMailTransportSettings($mail_options);
        $setting->adjust('test@ilias-test.de', 'someone@php-test.net');

        $this->assertSame(2, $mail_options->getIncomingType());
        $this->assertSame(5, $mail_options->getEmailAddressMode());
    }
}
