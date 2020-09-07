<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailOptionsGUITest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailOptionsGUITest extends \ilMailBaseTest
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \ilCtrl $ctrl
     * @param \ilSetting $settings
     * @return \ilMailOptionsGUI
     */
    protected function getMailOptionsGUI(
        \Psr\Http\Message\ServerRequestInterface $request,
        \ilCtrl $ctrl,
        \ilSetting $settings
    ) {
        $tpl = $this->getMockBuilder(\ilTemplate::class)->disableOriginalConstructor()->getMock();
        $lng = $this->getMockBuilder(\ilLanguage::class)->disableOriginalConstructor()->getMock();
        $user = $this->getMockBuilder(\ilObjUser::class)->disableOriginalConstructor()->getMock();
        $mail = $this->getMockBuilder(\ilFormatMail::class)->disableOriginalConstructor()->getMock();
        $mailBox = $this->getMockBuilder(\ilMailbox::class)->disableOriginalConstructor()->getMock();

        return new \ilMailOptionsGUI(
            $tpl,
            $ctrl,
            $settings,
            $lng,
            $user,
            $request,
            $mail,
            $mailBox
        );
    }

    /**
     *
     */
    public function testMailOptionsAreAccessibleIfGlobalAccessIsNotDenied()
    {
        $request = $this->getMockBuilder(\Psr\Http\Message\ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $ctrl = $this->getMockBuilder(\ilCtrl::class)->disableOriginalConstructor()->getMock();
        $settings = $this->getMockBuilder(\ilSetting::class)->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder(\ilMailOptionsFormGUI::class)->disableOriginalConstructor()->getMock();

        $settings->expects($this->any())->method('get')->with('show_mail_settings')->willReturn('1');
        $ctrl->expects($this->any())->method('getCmd')->willReturn('showOptions');
        $request->expects($this->any())->method('getQueryParams')->willReturn([]);

        $gui = $this->getMailOptionsGUI($request, $ctrl, $settings);
        $gui->setForm($form);
        $gui->executeCommand();
    }

    public function testMailOptionsAreNotAccessibleIfGlobalAccessIsDeniedAndUserWillBeRedirectedToMailSystem()
    {
        $request = $this->getMockBuilder(\Psr\Http\Message\ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $ctrl = $this->getMockBuilder(\ilCtrl::class)->disableOriginalConstructor()->getMock();
        $settings = $this->getMockBuilder(\ilSetting::class)->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder(\ilMailOptionsFormGUI::class)->disableOriginalConstructor()->getMock();

        $settings->expects($this->any())->method('get')->with('show_mail_settings')->willReturn('0');
        $ctrl->expects($this->any())->method('getCmd')->willReturn('showOptions');
        $request->expects($this->any())->method('getQueryParams')->willReturn([]);

        $ctrl->expects($this->once())->method('redirectByClass')->with('ilMailGUI');

        $gui = $this->getMailOptionsGUI($request, $ctrl, $settings);
        $gui->setForm($form);
        $gui->executeCommand();
    }

    public function testMailOptionsAreNotAccessibleIfGlobalAccessIsDeniedAndUserWillBeRedirectedToPersonalSettings()
    {
        $request = $this->getMockBuilder(\Psr\Http\Message\ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $ctrl = $this->getMockBuilder(\ilCtrl::class)->disableOriginalConstructor()->getMock();
        $settings = $this->getMockBuilder(\ilSetting::class)->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder(\ilMailOptionsFormGUI::class)->disableOriginalConstructor()->getMock();

        $settings->expects($this->any())->method('get')->with('show_mail_settings')->willReturn('0');
        $ctrl->expects($this->any())->method('getCmd')->willReturn('showOptions');

        $ctrl->expects($this->once())->method('redirectByClass')->with('ilPersonalSettingsGUI');
        $request->expects($this->any())->method('getQueryParams')->willReturn([
            'referrer' => 'ilPersonalSettingsGUI'
        ]);

        $gui = $this->getMailOptionsGUI($request, $ctrl, $settings);
        $gui->setForm($form);
        $gui->executeCommand();
    }
}
