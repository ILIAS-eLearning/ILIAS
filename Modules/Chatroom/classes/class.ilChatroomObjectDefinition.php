<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author jposselt@databay.de
 */
class ilChatroomObjectDefinition
{
	/**
	 * Module name, defaults to 'Chatroom'
	 * @var string
	 */
	private $moduleName;

	/**
	 * Module base path, set to "Modules/$this->moduleName/"
	 * @var string
	 */
	private $moduleBasePath;

	/**
	 * always set to 'classes'
	 * @var string
	 */
	private $relativeClassPath;

	/**
	 * TaskScope
	 * set to '' for single instance or 'admin' for general administration
	 * @var string
	 */
	private $taskScope;

	/**
	 * Sets class parameters using given parameters.
	 * @param string $moduleName
	 * @param string $moduleBasePath
	 * @param string $relativeClassPath Optional.
	 * @param string $taskScope         Optional.
	 */
	public function __construct($moduleName, $moduleBasePath, $relativeClassPath = 'classes', $taskScope = '')
	{
		$this->moduleName        = $moduleName;
		$this->moduleBasePath    = rtrim($moduleBasePath, '/\\');
		$this->relativeClassPath = rtrim($relativeClassPath);
		$this->taskScope         = rtrim($taskScope);
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
	 * and $taskScope as parameters.
	 * @param string $moduleName
	 * @param string $taskScope Optional. 'admin' or ''. Default ''
	 * @return ilChatroomObjectDefinition
	 */
	public static function getDefaultDefinitionWithCustomTaskPath($moduleName, $taskScope = '')
	{
		$object = new self(
			$moduleName, 'Modules/' . $moduleName . '/', 'classes', $taskScope
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
	 * Builds task path using given $task and returns it.
	 * @param string $task
	 * @return string
	 */
	public function getTaskPath($task)
	{
		return $this->moduleBasePath . '/' . $this->relativeClassPath . '/' .
		$this->taskScope . 'tasks/class.' . $this->getTaskClassName($task) . '.php';
	}

	/**
	 * Builds task classname using given $task and returns it.
	 * @param string $task
	 * @return string
	 */
	public function getTaskClassName($task)
	{
		return 'il' . $this->moduleName . ucfirst($this->taskScope) . ucfirst($task) . 'Task';
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
}