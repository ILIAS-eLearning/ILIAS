<?php

require_once dirname(__FILE__) . '/../class.ilChatroomAbstractTaskTest.php';

/**
 * Class ilChatroomPostMessageTaskTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
class ilChatroomPostMessageTaskTest extends ilChatroomAbstractTaskTest
{
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject|ilChatroomPostMessageTask;
	 */
	protected $task;

	protected function setUp()
	{
		parent::setUp();

		require_once './Modules/Chatroom/classes/tasks/class.ilChatroomPostMessageTask.php';

		$this->createIlObjChatroomMock(15);
		$this->createIlObjChatroomGUIMock($this->object);

		$this->task = $this->getMock(
			'ilChatroomPostMessageTask',
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