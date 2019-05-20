<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilMailTaskProcessorTest extends \ilMailBaseTest
{

	private $languageMock;
	private $dicMock;
	private $loggerMock;

	public function setUp(): void
	{
		$this->languageMock = $this->getMockBuilder('ilLanguage')
			->disableOriginalConstructor()
			->getMock();

		$this->dicMock = $this->getMockBuilder('\ILIAS\DI\Container')
			->disableOriginalConstructor()
			->getMock();

		$this->loggerMock = $this->getMockBuilder('ilLogger')
			->disableOriginalConstructor()
			->getMock();
	}
	/**
	 * @throws ilException
	 */
	public function testOneTask()
	{
		$taskManager = $this->getMockBuilder('\ILIAS\BackgroundTasks\Implementation\TaskManager\BasicTaskManager')
			->setMethods(array('run'))
			->disableOriginalConstructor()
			->getMock();

		$taskManager
			->expects($this->exactly(1))
			->method('run');

		$taskFactory = $this->getMockBuilder('ILIAS\BackgroundTasks\Task\TaskFactory')
			->setMethods(array('createTask'))
			->disableOriginalConstructor()
			->getMock();

		$backgroundTask = $this->getMockbuilder('ilMailDeliveryJob')
			->disableOriginalConstructor()
			->getMock();

		$backgroundTask->method('unfoldTask')
			->willReturn(array());

		$taskFactory
			->expects($this->exactly(2))
			->method('createTask')
			->willReturn($backgroundTask);


		$worker = new ilMassMailTaskProcessor(
			$taskManager,
			$taskFactory,
			$this->languageMock,
			$this->loggerMock,
			$this->dicMock,
			new ilMailValueObjectJsonService(),
			'SomeAnonymousUserId'
		);

		$mailValueObject = new ilMailValueObject(
			'ilias@server.com',
			'somebody@iliase.de',
			'',
			'',
			'That is awesome!',
			'Dear Steve, great!',
			null,
			array()
		);

		$mailValueObjects = array(
			$mailValueObject
		);

		$userId = 100;
		$contextId = 5;
		$contextParameters = array();

		$worker->run(
			$mailValueObjects,
			$userId,
			$contextId,
			$contextParameters
		);
	}

	/**
	 * @throws ilException
	 */
	public function testRunTwoTasks()
	{
		$taskManager = $this->getMockBuilder('\ILIAS\BackgroundTasks\Implementation\TaskManager\BasicTaskManager')
			->setMethods(array('run'))
			->disableOriginalConstructor()
			->getMock();

		$taskManager
			->expects($this->exactly(1))
			->method('run');

		$taskFactory = $this->getMockBuilder('ILIAS\BackgroundTasks\Task\TaskFactory')
			->setMethods(array('createTask'))
			->disableOriginalConstructor()
			->getMock();

		$backgroundTask = $this->getMockbuilder('ilMailDeliveryJob')
			->disableOriginalConstructor()
			->getMock();

		$backgroundTask->method('unfoldTask')
			->willReturn(array());

		$taskFactory
			->expects($this->exactly(2))
			->method('createTask')
			->willReturn($backgroundTask);

		$worker = new ilMassMailTaskProcessor(
			$taskManager,
			$taskFactory,
			$this->languageMock,
			$this->loggerMock,
			$this->dicMock,
			new ilMailValueObjectJsonService(),
			'SomeAnonymousUserId'
		);

		$mailValueObjects = array();

		$mailValueObjects[] = new ilMailValueObject(
			'ilias@server.com',
			'somebody@iliase.de',
			'',
			'',
			'That is awesome!',
			'Dear Steve, great!',
			null
		);

		$mailValueObjects[] = new ilMailValueObject(
			'ilias@server.com',
			'somebodyelse@iliase.de',
			'',
			'',
			'Greate',
			'Steve, Steve, Steve. Wait that is not Steve',
			null
		);

		$userId = 100;
		$contextId = 5;
		$contextParameters = array();

		$worker->run(
			$mailValueObjects,
			$userId,
			$contextId,
			$contextParameters
		);
	}

	/**
	 * @throws ilException
	 */
	public function testRunThreeTasksInDifferentBuckets()
	{
		$taskManager = $this->getMockBuilder('\ILIAS\BackgroundTasks\Implementation\TaskManager\BasicTaskManager')
			->setMethods(array('run'))
			->disableOriginalConstructor()
			->getMock();

		$taskManager
			->expects($this->exactly(2))
			->method('run');

		$taskFactory = $this->getMockBuilder('ILIAS\BackgroundTasks\Task\TaskFactory')
			->setMethods(array('createTask'))
			->disableOriginalConstructor()
			->getMock();

		$backgroundTask = $this->getMockbuilder('ilMailDeliveryJob')
			->disableOriginalConstructor()
			->getMock();

		$backgroundTask->method('unfoldTask')
			->willReturn(array());

		$taskFactory
			->expects($this->exactly(4))
			->method('createTask')
			->willReturn($backgroundTask);

		$worker = new ilMassMailTaskProcessor(
			$taskManager,
			$taskFactory,
			$this->languageMock,
			$this->loggerMock,
			$this->dicMock,
			new ilMailValueObjectJsonService(),
			'SomeAnonymousUserId'
		);

		$mailValueObjects = array();

		$mailValueObjects[] = new ilMailValueObject(
			'ilias@server.com',
			'somebody@iliase.de',
			'',
			'',
			'That is awesome!',
			'Dear Steve, great!',
			null
		);

		$mailValueObjects[] = new ilMailValueObject(
			'ilias@server.com',
			'somebodyelse@iliase.de',
			'',
			'',
			'Greate',
			'Steve, Steve, Steve. Wait that is not Steve',
			null
		);

		$mailValueObjects[] = new ilMailValueObject(
			'ilias@server.com',
			'somebody@iliase.de',
			'',
			'',
			'That is awesome!',
			'Hey Alan! Alan! Alan!',
			null
		);

		$userId = 100;
		$contextId = 5;
		$contextParameters = array();

		$worker->run(
			$mailValueObjects,
			$userId,
			$contextId,
			$contextParameters,
			2
		);
	}

	/**
	 * 
	 */
	public function testRunHasWrongTypeAndWillResultInException()
	{
		$this->expectException(\ilException::class);

		$taskManager = $this->getMockBuilder('\ILIAS\BackgroundTasks\Implementation\TaskManager\BasicTaskManager')
			->setMethods(array('run'))
			->disableOriginalConstructor()
			->getMock();

		$taskManager
			->expects($this->never())
			->method('run');

		$taskFactory = $this->getMockBuilder('ILIAS\BackgroundTasks\Task\TaskFactory')
			->setMethods(array('createTask'))
			->disableOriginalConstructor()
			->getMock();

		$backgroundTask = $this->getMockbuilder('ilMailDeliveryJob')
			->disableOriginalConstructor()
			->getMock();

		$backgroundTask->method('unfoldTask')
			->willReturn(array());

		$taskFactory
			->expects($this->never())
			->method('createTask')
			->willReturn($backgroundTask);

		$worker = new ilMassMailTaskProcessor(
			$taskManager,
			$taskFactory,
			$this->languageMock,
			$this->loggerMock,
			$this->dicMock,
			new ilMailValueObjectJsonService(),
			'SomeAnonymousUserId'
		);

		$mailValueObjects = array();

		$mailValueObjects[] = new ilMailValueObject(
			'ilias@server.com',
			'somebody@iliase.de',
			'',
			'',
			'That is awesome!',
			'Dear Steve, great!',
			null
		);

		$mailValueObjects[] = new ilMailValueObject(
			'ilias@server.com',
			'somebodyelse@iliase.de',
			'',
			'',
			'Greate',
			'Steve, Steve, Steve. Wait that is not Steve',
			null
		);

		$mailValueObjects[] = 'This should fail';

		$userId = 100;
		$contextId = 5;
		$contextParameters = array();

		$worker->run(
			$mailValueObjects,
			$userId,
			$contextId,
			$contextParameters,
			2
		);
	}
}
