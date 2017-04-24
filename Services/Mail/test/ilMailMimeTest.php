<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/test/ilMailBaseTest.php';
require_once 'Services/Mail/classes/Mime/Transport/class.ilMailMimeTransportFactory.php';
require_once 'Services/Mail/classes/Mime/Transport/interface.ilMailMimeTransport.php';
require_once 'Services/Mail/classes/class.ilMimeMail.php';

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeTest extends \ilMailBaseTest
{
	/**
	 * 
	 */
	protected function setUp()
	{
		\ilMimeMail::setDefaultTransport(null);

		parent::setUp();
	}

	/**
	 * 
	 */
	public function testMimMailDelegatesEmailDeliveryToThePassedTransporter()
	{
		$defaultTransport = $this->getMockBuilder('\ilMailMimeTransport')->getMock();
		$defaultTransport->expects($this->never())->method('send');

		$transport = $this->getMockBuilder('\ilMailMimeTransport')->getMock();
		$transport->expects($this->once())->method('send');

		$transportFactory = $this->getMockBuilder('\ilMailMimeTransportFactory')->disableOriginalConstructor()->getMock();
		$transportFactory->expects($this->any())->method('getTransport')->will($this->returnValue($defaultTransport));
		$this->setGlobalVariable('mail.mime.transport.factory', $transportFactory);

		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->setMethods(array('set', 'get'))->getMock();
		$this->setGlobalVariable('ilSetting', $settings);

		$mail = new \ilMimeMail();
		$mail->send($transport);
	}

	/**
	 *
	 */
	public function testMimMailDelegatesEmailDeliveryToDefaultTransport()
	{
		$defaultTransport = $this->getMockBuilder('\ilMailMimeTransport')->getMock();
		$defaultTransport->expects($this->once())->method('send');

		$transportFactory = $this->getMockBuilder('\ilMailMimeTransportFactory')->disableOriginalConstructor()->getMock();
		$transportFactory->expects($this->any())->method('getTransport')->will($this->returnValue($defaultTransport));
		$this->setGlobalVariable('mail.mime.transport.factory', $transportFactory);

		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->setMethods(array('set', 'get'))->getMock();
		$this->setGlobalVariable('ilSetting', $settings);

		$mail = new \ilMimeMail();
		$mail->send();
	}
}