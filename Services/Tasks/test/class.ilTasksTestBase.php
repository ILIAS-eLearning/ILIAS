<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/bootstrap.php';

/**
 * Base test class for tasks tests
 *
 * @author killing@leifos.de
 */
class ilTasksTestBase extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var bool
	 */
	protected $backupGlobals = false;

	protected $_mock_user;
	protected $_mock_lng;
	protected $_mock_ui;
	protected $_mock_access;
	protected $_mock_task_service;
	protected $_mock_dic;

	/**
	 *
	 */
	public function setUp()
	{
		$this->_mock_user = $this->getMockBuilder('ilObjUser')
			->disableOriginalConstructor()
			->getMock();

		$this->_mock_lng = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$this->_mock_ui = $this->getMockBuilder('\ILIAS\DI\UIServices')
			->disableOriginalConstructor()
			->getMock();

		$this->_mock_access = $this->getMockBuilder('ilAccessHandler')
			->disableOriginalConstructor()
			->getMock();

		require_once __DIR__ . '/class.ilDummyDerivedTaskProvider.php';
		require_once __DIR__ . '/class.ilDummyDerivedTaskProviderFactory.php';

		$master_factory_mock = $this->getMockBuilder('ilDerivedTaskProviderMasterFactory')
			->disableOriginalConstructor()
			->setMethods(['getAllProviders'])
			->getMock();

		$this->_mock_task_service = new ilTaskService($this->_mock_user, $this->_mock_lng, $this->_mock_ui, $this->_mock_access,
			$master_factory_mock);

		$master_factory_mock->expects($this->any())
			->method('getAllProviders')
			->willReturn([new ilDummyDerivedTaskProvider($this->_mock_task_service)]);
	}

	function getTaskServiceMock()
	{
		return $this->_mock_task_service;
	}
}