<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ilDBayObject
 *
 * @author jposselt
 */
abstract class ilDBayObjectGUI extends ilObjectGUI
{

	/**
	 * @return ilDBayObjectDefinition
	 */
	abstract protected function getObjectDefinition();

	/**
	 * Loads end executes given $task.
	 * 
	 * @param string $task
	 * @param string $method 
	 */
	protected function dispatchCall($task, $method)
	{
		$definition = $this->getObjectDefinition();
		
		if( $definition->hasTask( $task ) )
		{
			$definition->loadTask( $task );
			$taskHandler = $definition->buildTask( $task, $this );
			$taskHandler->execute( $method );
		}
	}

	/**
	 * Calls $this->prepareOutput() method.
	 */
	public function switchToVisibleMode()
	{
		$this->prepareOutput();
	}

	public function getAdminTabs(&$tabs_gui) {
		global $tree;

		if ($_GET["admin_mode"] == "repository")
		{
			$this->ctrl->setParameterByClass("iladministrationgui", "admin_mode", "settings");
			$tabs_gui->setBackTarget($this->lng->txt("administration"),
				$this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"),
				ilFrameTargetInfo::_getFrame("MainContent"));
			$this->ctrl->setParameterByClass("iladministrationgui", "admin_mode", "repository");
		}

		if ($this->checkPermissionBool("edit_permission"))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), "", "ilpermissiongui");
		}

		if ($tree->getSavedNodeData($this->object->getRefId()))
		{
			$tabs_gui->addTarget("trash",
				$this->ctrl->getLinkTarget($this, "trash"), "trash", get_class($this));
		}
	}

}

?>
