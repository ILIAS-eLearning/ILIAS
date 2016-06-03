<?php

require_once dirname(__FILE__) . '/../class.ilChatroomAbstractTaskTest.php';

/**
 * Class ilChatroomInitialTaskTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomInitialTaskTest extends ilChatroomAbstractTaskTest
{

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject|ilChatroomInitialTask;
	 */
	protected $task;

	protected function setUp()
	{
		parent::setUp();

		require_once './Modules/Chatroom/classes/tasks/class.ilChatroomInitialTask.php';

		$this->createIlObjChatroomMock(15);
		$this->createIlObjChatroomGUIMock($this->object);

		$this->task = $this->getMock(
			'ilChatroomInitialTask',
			array('sendResponse', 'getRoomByObjectId', 'redirectIfNoPermission'),
			array($this->gui)
		);
	}

	public function testExecuteDefaultDies()
	{
		$this->setExpectedException('Exception', 'METHOD_NOT_IN_USE');
		$this->task->executeDefault(null);
	}
}