<?php

require_once 'Services/Mail/test/ilMailBaseTest.php';

class ilMailOptionsTest extends \ilMailBaseTest
{
	public function testConstructor()
	{
		$userId = 1;

		$database = $this->getMockBuilder('ilDBInterface')
			->disableOriginalConstructor()
			->getMock();
		$queryMock = $this->getMockBuilder('ilPDOStatement')
			->disableOriginalConstructor()
			->setMethods(array('fetchRow'))
			->getMock();

		$object = $this->getMockBuilder(stdClass::class)->getMock();
		$object->cronjob_notification = false;
		$object->signature = 'smth';
		$object->linebreak = false;
		$object->incoming_type = 'MY';
		$object->mail_address_option = 0;
		$object->email = 'test@test.com';
		$object->second_email = 'ilias@ilias.com';


		$queryMock->method('fetchRow')->willReturn($object);
		$database->expects($this->atLeastOnce())->method('queryF')->willReturn($queryMock);
		$database->method('replace')->willReturn(0);

		$this->setGlobalVariable('ilDB', $database);

		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->setMethods(array('set', 'get'))->getMock();
		$this->setGlobalVariable('ilSetting', $settings);

		$mailOptions = new ilMailOptions($userId);
	}
}

