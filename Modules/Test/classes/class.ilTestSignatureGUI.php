<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Export/classes/class.ilExportGUI.php';

/**
 * Signature Plugin Interface Class
 * @author       Maximilian Becker <mbecker@databay.de>
 * @version      $Id$
 * @ingroup      ModulesTest
 * @ilCtrl_isCalledBy ilTestSignatureGUI: ilTestOutputGUI
 */
class ilTestSignatureGUI 
{
	/** @var \ilLanguage */
	protected $lng;

	/** @var $ilCtrl ilCtrl */
	protected $ilCtrl;

	/** @var \ilTemplate  */
	protected $tpl;

	/** @var \ilObjTestGUI */
	protected $testGUI;

	/** @var \ilObjTest */
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
			default:
				$ret = $this->dispatchCommand();
				break;
		}
		return $ret;
	}

	protected function dispatchCommand()
	{
		$cmd = $this->ilCtrl->getCmd();
		switch ($cmd)
		{
			default:
				$ret = $this->plugin->invoke($cmd);
		}

		return $ret;
	}
}