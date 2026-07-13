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

use ILIAS\HTTP\Wrapper\WrapperFactory;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use ILIAS\User\Settings\Settings as UserSettings;
use ILIAS\User\Settings\PersonalSettingsGUI;

class ilMailOptionsGUITest extends ilMailBaseTestCase
{
    protected function getMailOptionsGUI(
        GlobalHttpState $http_state,
        ilCtrlInterface $ctrl,
        ilMailOptions $mail_options
    ): ilMailOptionsGUI {
        $tpl = $this->getMockBuilder(ilGlobalTemplateInterface::class)->disableOriginalConstructor()->getMock();
        $lng = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();

        return new ilMailOptionsGUI(
            $tpl,
            $ctrl,
            $lng,
            $user,
            $http_state,
            new Factory(new \ILIAS\Data\Factory(), $lng),
            $mail_options
        );
    }

    #[DoesNotPerformAssertions]
    public function testMailOptionsAreAccessibleIfGlobalAccessIsNotDenied(): void
    {
        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder(ilMailOptionsFormGUI::class)->disableOriginalConstructor()->getMock();

        $ctrl->method('getCmd')->willReturn('showOptions');

        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $request->method('getQueryParams')->willReturn([]);
        $wrapper = new WrapperFactory($request);

        $http = $this->getMockBuilder(GlobalHttpState::class)->getMock();
        $http->method('wrapper')->willReturn($wrapper);

        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods(['get'])->getMock();
        $settings->method('get')->willReturnCallback(static function (string $key, ?string $default = null) {
            if ($key === 'show_mail_settings') {
                return '1';
            }

            return $default;
        });

        $options = new ilMailOptions(
            0,
            null,
            $this->createMock(\ILIAS\Data\Clock\ClockInterface::class),
            $settings,
            $this->createMock(ilDBInterface::class),
            $this->createMock(UserSettings::class)
        );

        $gui = $this->getMailOptionsGUI($http, $ctrl, $options);
        $gui->setForm($form);
        $gui->executeCommand();
    }

    public function testMailOptionsAreNotAccessibleIfGlobalAccessIsDeniedAndUserWillBeRedirectedToMailSystem(): void
    {
        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder(ilMailOptionsFormGUI::class)->disableOriginalConstructor()->getMock();

        $ctrl->method('getCmd')->willReturn('showOptions');

        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $request->method('getQueryParams')->willReturn([]);
        $wrapper = new WrapperFactory($request);

        $http = $this->getMockBuilder(GlobalHttpState::class)->getMock();
        $http->method('wrapper')->willReturn($wrapper);

        $ctrl->expects($this->once())->method('redirectByClass')->with(ilMailGUI::class);

        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods(['get'])->getMock();
        $settings->method('get')->willReturnCallback(static function (string $key, ?string $default = null) {
            if ($key === 'show_mail_settings') {
                return '0';
            }

            return $default;
        });

        $options = new ilMailOptions(
            0,
            null,
            $this->createMock(\ILIAS\Data\Clock\ClockInterface::class),
            $settings,
            $this->createMock(ilDBInterface::class),
            $this->createMock(UserSettings::class)
        );

        $gui = $this->getMailOptionsGUI($http, $ctrl, $options);
        $gui->setForm($form);
        $gui->executeCommand();
    }
}
