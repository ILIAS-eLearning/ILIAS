<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author jposselt@databay.de
 */
class ilChatroomObjectDefinition
{
	/**
	 * @var string
	 */
	private $moduleName;

	/**
	 * @var string
	 */
	private $moduleBasePath;

	/**
	 * @var string
	 */
	private $relativeClassPath;

	/**
	 * @var string
	 */
	private $relativeTaskPath;

	/**
	 * Sets class parameters using given parameters.
	 * @param string $moduleName
	 * @param string $moduleBasePath
	 * @param string $relativeClassPath
	 * @param string $relativeTaskPath
	 */
	public function __construct($moduleName, $moduleBasePath, $relativeClassPath = 'classes', $relativeTaskPath = 'tasks')
	{
		$this->moduleName        = $moduleName;
		$this->moduleBasePath    = rtrim($moduleBasePath, '/\\');
		$this->relativeClassPath = rtrim($relativeClassPath);
		$this->relativeTaskPath  = rtrim($relativeTaskPath);
	}

	/**
	 * Returns an Instance of ilChatroomObjectDefinition, using given $moduleName
	 * as parameter.
	 * @param string $moduleName
	 * @return ilChatroomObjectDefinition
	 */
	public static function getDefaultDefinition($moduleName)
	{
		$object = new self($moduleName, 'Modules/' . $moduleName . '/');

		return $object;
	}

	/**
	 * Returns an Instance of ilChatroomObjectDefinition, using given $moduleName
	 * and $relativeTaskFolder as parameters.
	 * @param string $moduleName
	 * @param string $relativeTaskFolder
	 * @return ilChatroomObjectDefinition
	 */
	public static function getDefaultDefinitionWithCustomTaskPath($moduleName, $relativeTaskFolder)
	{
		$object = new self(
			$moduleName, 'Modules/' . $moduleName . '/', 'classes', $relativeTaskFolder
		);

		return $object;
	}

	/**
	 * Returns true if file exists.
	 * @param string $task
	 * @return boolean
	 */
	public function hasTask($task)
	{
		return file_exists($this->getTaskPath($task));
	}

	/**
	 * Requires file, whereby given $task is used as parameter in getTaskPath
	 * method to build the filename of the file to required.
	 * @param string $task
	 */
	public function loadTask($task)
	{
		require_once $this->getTaskPath($task);
	}


	/**
	 * Builds and returns new task using given $task and $gui
	 * @param string              $task
	 * @param ilChatroomObjectGUI $gui
	 * @return ilChatroomTaskHandler
	 */
	public function buildTask($task, ilChatroomObjectGUI $gui)
	{
		$className = $this->getTaskClassName($task);
		$task      = new $className($gui);

		return $task;
	}

	/**
	 * Builds task classname using given $task and returns it.
	 * @param string $task
	 * @return string
	 */
	public function getTaskClassName($task)
	{
		return 'il' . $this->moduleName . ucfirst($task) . 'Task';
	}

	/**
	 * Builds task path using given $task and returns it.
	 * @param string $task
	 * @return string
	 */
	public function getTaskPath($task)
	{
		return $this->moduleBasePath . '/' . $this->relativeClassPath . '/' .
			$this->relativeTaskPath . '/class.' . $this->getTaskClassName($task) . '.php';
	}
}