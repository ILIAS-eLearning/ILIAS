<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Export/classes/class.ilExportGUI.php';

/**
 * Signature Plugin Class
 * @author       Maximilian Becker <mbecker@databay.de>
 *               
 * @version      $Id$
 *               
 * @ingroup      ModulesTest
 *               
 * @ilCtrl_isCalledBy 	ilTestSignatureGUI: ilTestOutputGUI
 * @ilCtrl_calls 		ilTestSignatureGUI: ilTestOutputGUI, ilTestEvaluationGUI
 */
class ilTestSignatureGUI 
{
	/** @var $lng \ilLanguage */
	protected $lng;

	/** @var $ilCtrl ilCtrl */
	protected $ilCtrl;

	/** @var $tpl \ilTemplate  */
	protected $tpl;

	/** @var $testGUI \ilObjTestGUI */
	protected $testGUI;
	
	/** @var $ilTestOutputGUI \ilTestOutputGUI */
	protected $ilTestOutputGUI;

	/** @var $test \ilObjTest */
	protected $test;

	/** @var \ilTestSignaturePlugin */
	protected $plugin;

	public function __construct(ilTestOutputGUI $testOutputGUI)
	{
		global $lng, $ilCtrl, $tpl, $ilPluginAdmin;
		
		$this->lng = $lng;
		$this->ilCtrl = $ilCtrl;
		$this->tpl = $tpl;

		$this->ilTestOutputGUI = $testOutputGUI;
		$this->test = $this->ilTestOutputGUI->object;

		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, 'Test', 'tsig');
		$pl = current($pl_names);
		$this->plugin = ilPluginAdmin::getPluginObject(IL_COMP_MODULE, 'Test', 'tsig', $pl);
		$this->plugin->setGUIObject($this);
	}

	public function executeCommand()
	{
		$next_class = $this->ilCtrl->getNextClass($this);

		switch($next_class)
		{
			case 'iltestoutputgui':
				$ret = $this->ilCtrl->forwardCommand($this->ilTestOutputGUI);
				break;
			
			case 'iltestevaluationgui':
				require_once './Modules/Test/classes/class.ilTestEvaluationGUI.php';
				$evaluation_gui = new ilTestEvaluationGUI($this->getTest());
				$this->ilCtrl->forwardCommand($evaluation_gui);
				break;
			
			default:
				$ret = $this->dispatchCommand();
				break;
		}
		return $ret;
	}

	protected function dispatchCommand()
	{
		/** @var $ilUser ilObjUser */
		global $ilUser;
		$active = $this->test->getActiveIdOfUser($ilUser->getId());
		$pass = $this->test->_getMaxPass($active);
		$key = 'signed_'. $active .'_'. $pass;
		ilSession::set($key, null);

		$cmd = $this->ilCtrl->getCmd();
		switch ($cmd)
		{
			default:
				$ret = $this->plugin->invoke($cmd);
		}
		return $ret;
	}

	/**
	 * @param \ilObjTest $test
	 */
	public function setTest($test)
	{
		$this->test = $test;
	}

	/**
	 * @return \ilObjTest
	 */
	public function getTest()
	{
		return $this->test;
	}

	/**
	 * @param \ilObjTestGUI $testGUI
	 */
	public function setTestGUI($testGUI)
	{
		$this->testGUI = $testGUI;
	}

	/**
	 * @return \ilObjTestGUI
	 */
	public function getTestGUI()
	{
		return $this->testGUI;
	}

	/**
	 * This is to be called by the plugin at the end of the signature process to redirect the user back to the test.
	 */
	public function redirectToTest($success)
	{
		/** @var $ilCtrl ilCtrl */
		/** @var $ilUser ilObjUser */
		global $ilCtrl, $ilUser;
		$active = $this->test->getActiveIdOfUser($ilUser->getId());
		$pass = $this->test->_getMaxPass($active);
		$key = 'signed_'. $active .'_'. $pass;
		ilSession::set($key, $success);
		$ilCtrl->redirectByClass('ilTestOutputGUI','afterTestPassFinished');
		return;
	}
}