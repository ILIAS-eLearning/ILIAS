<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilMailOptionsGUITest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailOptionsGUITest extends ilMailBaseTest
{
    /**
     * @throws ReflectionException
     */
    protected function getMailOptionsGUI(
        ServerRequestInterface $request,
        ilCtrl $ctrl,
        ilMailOptions $mail_options
    ) : ilMailOptionsGUI {
        $tpl = $this->getMockBuilder(ilGlobalPageTemplate::class)->disableOriginalConstructor()->getMock();
        $lng = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $mail = $this->getMockBuilder(ilFormatMail::class)->disableOriginalConstructor()->getMock();
        $mailBox = $this->getMockBuilder(ilMailbox::class)->disableOriginalConstructor()->getMock();

        return new ilMailOptionsGUI(
            $tpl,
            $ctrl,
            $lng,
            $user,
            $request,
            $mail,
            $mailBox,
            $mail_options
        );
    }

    /**
     * @doesNotPerformAssertions
     * @throws ReflectionException
     */
    public function testMailOptionsAreAccessibleIfGlobalAccessIsNotDenied() : void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $request->method('getQueryParams')->willReturn([]);
        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $ctrl->method('getCmd')->willReturn('showOptions');
        $form = $this->getMockBuilder(ilMailOptionsFormGUI::class)->disableOriginalConstructor()->getMock();
        $db = $this->createMock(ilDBInterface::class);

        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods(['get'])->getMock();
        $settings->method('get')->willReturnCallback(static function (string $key, $default = false) {
            if ($key === 'show_mail_settings') {
                return '1';
            }

            return $default;
        });

        $options = new ilMailOptions(
            0,
            null,
            $settings,
            $db
        );

        $gui = $this->getMailOptionsGUI($request, $ctrl, $options);
        $gui->setForm($form);
        $gui->executeCommand();
    }

    /**
     * @throws ReflectionException
     */
    public function testMailOptionsAreNotAccessibleIfGlobalAccessIsDeniedAndUserWillBeRedirectedToMailSystem() : void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $request->method('getQueryParams')->willReturn([]);
        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $ctrl->method('getCmd')->willReturn('showOptions');
        $form = $this->getMockBuilder(ilMailOptionsFormGUI::class)->disableOriginalConstructor()->getMock();
        $db = $this->createMock(ilDBInterface::class);

        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods(['get'])->getMock();
        $settings->method('get')->willReturnCallback(static function (string $key, $default = false) {
            if ($key === 'show_mail_settings') {
                return '0';
            }

            return $default;
        });

        $ctrl->expects($this->once())->method('redirectByClass')->with('ilMailGUI');

        $options = new ilMailOptions(
            0,
            null,
            $settings,
            $db
        );
        
        $gui = $this->getMailOptionsGUI($request, $ctrl, $options);
        $gui->setForm($form);
        $gui->executeCommand();
    }

    /**
     * @throws ReflectionException
     */
    public function testMailOptionsAreNotAccessibleIfGlobalAccessIsDeniedAndUserWillBeRedirectedToPersonalSettings() : void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $request->method('getQueryParams')->willReturn([
            'referrer' => 'ilPersonalSettingsGUI'
        ]);
        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $ctrl->expects($this->once())->method('redirectByClass')->with('ilPersonalSettingsGUI');
        $ctrl->method('getCmd')->willReturn('showOptions');
        $form = $this->getMockBuilder(ilMailOptionsFormGUI::class)->disableOriginalConstructor()->getMock();
        $db = $this->createMock(ilDBInterface::class);

        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods(['get'])->getMock();
        $settings->method('get')->willReturnCallback(static function (string $key, $default = false) {
            if ($key === 'show_mail_settings') {
                return '0';
            }

            return $default;
        });

        $options = new ilMailOptions(
            0,
            null,
            $settings,
            $db
        );

        $gui = $this->getMailOptionsGUI($request, $ctrl, $options);
        $gui->setForm($form);
        $gui->executeCommand();
    }
}
