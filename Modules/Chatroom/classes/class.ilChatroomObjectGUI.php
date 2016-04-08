<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroomObjectDefinition.php';
require_once 'Modules/Chatroom/classes/class.ilChatroomTaskHandler.php';
require_once 'Services/UICore/classes/class.ilFrameTargetInfo.php';

/**
 * @author jposselt@databay.de
 * @abstract
 */
abstract class ilChatroomObjectGUI extends ilObjectGUI
{
	/**
	 * Loads end executes given $task.
	 * @param string $task
	 * @param string $method
	 */
	protected function dispatchCall($task, $method)
	{
		/**
		 * @var $definition ilChatroomObjectDefinition
		 */
		$definition = $this->getObjectDefinition();
		if($definition->hasTask($task))
		{
			$definition->loadTask($task);
			$taskHandler = $definition->buildTask($task, $this);
			$taskHandler->execute($method);
		}
	}

	/**
	 * @return ilChatroomObjectDefinition
	 * @abstract
	 */
	abstract protected function getObjectDefinition();

	/**
	 * @return ilChatroomServerConnector
	 * @abstract
	 */
	abstract public function getConnector();

	/**
	 * Calls $this->prepareOutput() method.
	 */
	public function switchToVisibleMode()
	{
		$this->prepareOutput();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAdminTabs()
	{
		/**
		 * @var $tree ilTree
		 */
		global $tree;

		if(isset($_GET['admin_mode']) && $_GET['admin_mode'] == 'repository')
		{
			$this->ctrl->setParameterByClass('iladministrationgui', 'admin_mode', 'settings');
			$this->tabs_gui->setBackTarget(
				$this->lng->txt('administration'),
				$this->ctrl->getLinkTargetByClass('iladministrationgui', 'frameset'),
				ilFrameTargetInfo::_getFrame('MainContent')
			);
			$this->ctrl->setParameterByClass('iladministrationgui', 'admin_mode', 'repository');
		}
		if($tree->getSavedNodeData($this->object->getRefId()))
		{
			$this->tabs_gui->addTarget('trash', $this->ctrl->getLinkTarget($this, 'trash'), 'trash', get_class($this));
		}
	}
}