<?php

/**
 * Created by PhpStorm.
 * User: bheyser
 * Date: 17.08.17
 * Time: 14:55
 */
class ilObjTestCtrl
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	
	/**
	 * @var ilObjTestGUI
	 */
	protected $testGUI;
	
	/**
	 * @var array
	 */
	protected $classPath = array();
	
	/**
	 * ilObjTestCtrl constructor.
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * @return ilCtrl
	 */
	public function getCtrl()
	{
		return $this->ctrl;
	}
	
	/**
	 * @param ilCtrl $ctrl
	 */
	public function setCtrl($ctrl)
	{
		$this->ctrl = $ctrl;
	}
	
	/**
	 * @return ilObjTestGUI
	 */
	public function getTestGUI()
	{
		return $this->testGUI;
	}
	
	/**
	 * @param ilObjTestGUI $testGUI
	 */
	public function setTestGUI($testGUI)
	{
		$this->testGUI = $testGUI;
	}
	
	protected function initClassPath()
	{
		if( !count($this->classPath) )
		{
			$this->getCtrl()->getPathNew;
		}
	}
	
	public function classPathContains()
	{
		
	}
}