<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author jposselt
 */
class ilDBayObjectDefinition
{

	private $moduleName;
	private $moduleBasePath;
	private $relativeClassPath;
	private $relativeTaskPath;

	/**
	 * Sets class parameters using given parameters.
	 * 
	 * @param string $moduleName
	 * @param string $moduleBasePath
	 * @param string $relativeClassPath
	 * @param string $relativeTaskPath 
	 */
	public function __construct($moduleName, $moduleBasePath, $relativeClassPath = 'classes', $relativeTaskPath = 'tasks')
	{
		$this->moduleName = $moduleName;
		$this->moduleBasePath = rtrim( $moduleBasePath, '/\\' );
		$this->relativeClassPath = rtrim( $relativeClassPath );
		$this->relativeTaskPath = rtrim( $relativeTaskPath );
	}

	/**
	 * Returns an Instance of ilDBayObjectDefinition, using given $moduleName
	 * as parameter.
	 * 
	 * @param string $moduleName
	 * @return ilDBayObjectDefinition 
	 */
	public static function getDefaultDefinition($moduleName)
	{
		$object = new self( $moduleName, 'Modules/' . $moduleName . '/' );

		return $object;
	}

	/**
	 * Returns an Instance of ilDBayObjectDefinition, using given $moduleName
	 * and $relativeTaskFolder as parameters.
	 * 
	 * @param string $moduleName
	 * @param string $relativeTaskFolder
	 * @return ilDBayObjectDefinition 
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
	 * 
	 * @param string $task
	 * @return boolean 
	 */
	public function hasTask($task)
	{
		return file_exists( $this->getTaskPath( $task ) );
	}

	/**
	 * Requires file, whereby given $task is used as parameter in getTaskPath
	 * method to build the filename of the file to required.
	 * 
	 * @param string $task 
	 */
	public function loadTask($task)
	{
		require_once $this->getTaskPath( $task );
	}


	/**
	 * Builds and returns new task using given $task and $gui
	 * 
	 * @param className $task
	 * @param ilDBayObjectGUI $gui
	 * @return className 
	 */
	public function buildTask($task, ilDBayObjectGUI $gui)
	{
		$className = $this->getTaskClassName( $task );
		$task = new $className( $gui );

		return $task;
	}

	/**
	 * Builds task classname using given $task and returns it.
	 *
	 * @param string $task
	 * @return string 
	 */
	public function getTaskClassName($task)
	{
		return 'il' . $this->moduleName . ucfirst( $task ) . 'Task';
	}

	/**
	 * Builds task path using given $task and returns it.
	 * 
	 * @param string $task
	 * @return string 
	 */
	public function getTaskPath($task)
	{
		return $this->moduleBasePath . '/' . $this->relativeClassPath . '/' .
			   $this->relativeTaskPath . '/class.' . $this->getTaskClassName( $task ) . '.php';
	}

}

?>
