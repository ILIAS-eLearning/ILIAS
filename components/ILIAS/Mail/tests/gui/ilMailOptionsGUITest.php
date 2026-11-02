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
use PHPUnit\Framework\MockObject\Stub;
use ILIAS\Data\Clock\ClockInterface;

class ilMailOptionsGUITest extends ilMailBaseTestCase
{
    protected function getMailOptionsGUI(
        GlobalHttpState $http_state,
        ilCtrlInterface $ctrl,
        ilMailOptions $mail_options
    ): ilMailOptionsGUI {
        /** @var ilGlobalTemplateInterface&Stub $tpl */
        $tpl = $this->getStubBuilder(ilGlobalTemplateInterface::class)->disableOriginalConstructor()->getStub();
        /** @var ilLanguage&Stub $lng */
        $lng = $this->getStubBuilder(ilLanguage::class)->disableOriginalConstructor()->getStub();
        /** @var ilObjUser&Stub $user */
        $user = $this->getStubBuilder(ilObjUser::class)->disableOriginalConstructor()->getStub();

        return new ilMailOptionsGUI(
            $tpl,
            $ctrl,
            $lng,
            $user,
            $http_state,
            new Factory(new ILIAS\Data\Factory(), $lng),
            $mail_options
        );
    }

    #[DoesNotPerformAssertions]
    public function testMailOptionsAreAccessibleIfGlobalAccessIsNotDenied(): void
    {
        /** @var ilCtrlInterface&Stub $ctrl */
        $ctrl = $this->createStub(ilCtrlInterface::class);
        /** @var ilMailOptionsFormGUI&Stub $form */
        $form = $this->getStubBuilder(ilMailOptionsFormGUI::class)->disableOriginalConstructor()->getStub();

        $ctrl->method('getCmd')->willReturn('showOptions');

        /** @var ServerRequestInterface&Stub $request */
        $request = $this->getStubBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getStub();
        $request->method('getQueryParams')->willReturn([]);
        $wrapper = new WrapperFactory($request);

        /** @var GlobalHttpState&Stub $http */
        $http = $this->createStub(GlobalHttpState::class);
        $http->method('wrapper')->willReturn($wrapper);

        /** @var ilSetting&Stub $settings */
        $settings = $this->getStubBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods(['get'])->getStub();
        $settings->method('get')->willReturnCallback(static function (string $key, ?string $default = null) {
            if ($key === 'show_mail_settings') {
                return '1';
            }

            return $default;
        });

        $options = new ilMailOptions(
            0,
            null,
            $this->createStub(ClockInterface::class),
            $settings,
            $this->createStub(ilDBInterface::class),
            $this->createStub(UserSettings::class)
        );

        $gui = $this->getMailOptionsGUI($http, $ctrl, $options);
        $gui->setForm($form);
        $gui->executeCommand();
    }

    public function testMailOptionsAreNotAccessibleIfGlobalAccessIsDeniedAndUserWillBeRedirectedToMailSystem(): void
    {
        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        /** @var ilMailOptionsFormGUI&Stub $form */
        $form = $this->getStubBuilder(ilMailOptionsFormGUI::class)->disableOriginalConstructor()->getStub();

        $ctrl->method('getCmd')->willReturn('showOptions');

        /** @var ServerRequestInterface&Stub $request */
        $request = $this->getStubBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getStub();
        $request->method('getQueryParams')->willReturn([]);
        $wrapper = new WrapperFactory($request);

        /** @var GlobalHttpState&Stub $http */
        $http = $this->createStub(GlobalHttpState::class);
        $http->method('wrapper')->willReturn($wrapper);

        $ctrl->expects($this->once())->method('redirectByClass')->with(ilMailGUI::class);

        /** @var ilSetting&Stub $settings */
        $settings = $this->getStubBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods(['get'])->getStub();
        $settings->method('get')->willReturnCallback(static function (string $key, ?string $default = null) {
            if ($key === 'show_mail_settings') {
                return '0';
            }

            return $default;
        });

        $options = new ilMailOptions(
            0,
            null,
            $this->createStub(ClockInterface::class),
            $settings,
            $this->createStub(ilDBInterface::class),
            $this->createStub(UserSettings::class)
        );

        $gui = $this->getMailOptionsGUI($http, $ctrl, $options);
        $gui->setForm($form);
        $gui->executeCommand();
    }
}
