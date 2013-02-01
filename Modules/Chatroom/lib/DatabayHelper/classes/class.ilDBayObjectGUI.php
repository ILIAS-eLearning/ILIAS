<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of ilDBayObject
 * @author jposselt
 * @abstract
 */
abstract class ilDBayObjectGUI extends ilObjectGUI
{
	/**
	 * @return ilDBayObjectDefinition
	 * @abstract
	 */
	abstract protected function getObjectDefinition();

	/**
	 * @return ilChatroomServerConnector
	 * @abstract
	 */
	abstract public function getConnector();

	/**
	 * Loads end executes given $task.
	 * @param string $task
	 * @param string $method
	 */
	protected function dispatchCall($task, $method)
	{
		$definition = $this->getObjectDefinition();

		if($definition->hasTask($task))
		{
			$definition->loadTask($task);
			$taskHandler = $definition->buildTask($task, $this);
			$taskHandler->execute($method);
		}
	}

	/**
	 * Calls $this->prepareOutput() method.
	 */
	public function switchToVisibleMode()
	{
		$this->prepareOutput();
	}

	/**
	 * @param ilTabsGUI $tabs_gui
	 */
	public function getAdminTabs(ilTabsGUI $tabs_gui)
	{
		/**
		 * @var $tree ilTree
		 */
		global $tree;

		if($_GET['admin_mode'] == 'repository')
		{
			$this->ctrl->setParameterByClass('iladministrationgui', 'admin_mode', 'settings');
			$tabs_gui->setBackTarget(
				$this->lng->txt('administration'),
				$this->ctrl->getLinkTargetByClass('iladministrationgui', 'frameset'),
				ilFrameTargetInfo::_getFrame('MainContent')
			);
			$this->ctrl->setParameterByClass('iladministrationgui', 'admin_mode', 'repository');
		}
		if($tree->getSavedNodeData($this->object->getRefId()))
		{
			$tabs_gui->addTarget('trash', $this->ctrl->getLinkTarget($this, 'trash'), 'trash', get_class($this));
		}
	}
}