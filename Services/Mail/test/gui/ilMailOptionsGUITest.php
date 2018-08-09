<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/test/ilMailBaseTest.php';
require_once 'Services/Mail/classes/Address/class.ilMailAddress.php';
require_once 'Services/Mail/classes/class.ilMailbox.php';
require_once 'Services/Mail/classes/class.ilFormatMail.php';

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailOptionsGUITest extends \ilMailBaseTest
{
	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param ilCtrl $ctrl
	 * @param ilSetting $setting
	 */
	protected function getMailOptionsGUI(
		\Psr\Http\Message\ServerRequestInterface $request,
		\ilCtrl $ctrl,
		\ilSetting $settings
	) {
		$tpl = $this->getMockBuilder('\ilTemplate')->disableOriginalConstructor()->getMock();
		$lng = $this->getMockBuilder('\ilLanguage')->disableOriginalConstructor()->getMock();
		$user = $this->getMockBuilder('\ilObjUser')->disableOriginalConstructor()->getMock();
		$mail = $this->getMockBuilder('\ilFormatMail')->disableOriginalConstructor()->getMock();
		$mailBox = $this->getMockBuilder('\ilMailBox')->disableOriginalConstructor()->getMock();

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
		$request = $this->getMockBuilder('\Psr\Http\Message\ServerRequestInterface')->disableOriginalConstructor()->getMock();
		$ctrl = $this->getMockBuilder('\ilCtrl')->disableOriginalConstructor()->getMock();
		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->getMock();
		$form = $this->getMockBuilder('\ilMailOptionsFormGUI')->disableOriginalConstructor()->getMock();

		$settings->expects($this->any())->method('get')->with('show_mail_settings')->willReturn('1');
		$ctrl->expects($this->any())->method('getCmd')->willReturn('showOptions');
		$request->expects($this->any())->method('getQueryParams')->willReturn([]);

		$gui = $this->getMailOptionsGUI($request, $ctrl, $settings);
		$gui->setForm($form);
		$gui->executeCommand();
	}

	public function testMailOptionsAreNotAccessibleIfGlobalAccessIsDeniedAndUserWillBeRedirectedToMailSystem()
	{
		$request = $this->getMockBuilder('\Psr\Http\Message\ServerRequestInterface')->disableOriginalConstructor()->getMock();
		$ctrl = $this->getMockBuilder('\ilCtrl')->disableOriginalConstructor()->getMock();
		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->getMock();
		$form = $this->getMockBuilder('\ilMailOptionsFormGUI')->disableOriginalConstructor()->getMock();

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
		$request = $this->getMockBuilder('\Psr\Http\Message\ServerRequestInterface')->disableOriginalConstructor()->getMock();
		$ctrl = $this->getMockBuilder('\ilCtrl')->disableOriginalConstructor()->getMock();
		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->getMock();
		$form = $this->getMockBuilder('\ilMailOptionsFormGUI')->disableOriginalConstructor()->getMock();

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