<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Component/classes/class.ilPlugin.php';

/**
 * Abstract parent class for all signature plugin classes.
 * 
 * @author  Maximilian Becker <mbecker@databay.de>
 * 
 * @version $Id$
 * 
 * @ingroup ModulesTest
 */
abstract class ilTestSignaturePlugin extends ilPlugin
{
	/** @var \ilTestSignatureGUI */
	protected $GUIObject;
	
	/**
	 * @param \ilTestSignatureGUI $GUIObject
	 */
	public function setGUIObject($GUIObject)
	{
		$this->GUIObject = $GUIObject;
	}

	/**
	 * @return \ilTestSignatureGUI
	 */
	public function getGUIObject()
	{
		return $this->GUIObject;
	}
	
	/**
	 * Get Component Type
	 * @return        string        Component Type
	 */
	final public function getComponentType()
	{
		return IL_COMP_MODULE;
	}

	/**
	 * Get Component Name.
	 * @return        string        Component Name
	 */
	final public function getComponentName()
	{
		return "Test";
	}

	/**
	 * Get Slot Name.
	 * @return        string        Slot Name
	 */
	final public function getSlot()
	{
		return "Signature";
	}

	/**
	 * Get Slot ID.
	 * @return        string        Slot Id
	 */
	final public function getSlotId()
	{
		return "tsig";
	}

	/**
	 * Object initialization done by slot.
	 */
	final protected function slotInit()
	{
	}

	/**
	 * @param string $cmd
	 *
	 * @return string
	 */
	protected function getLinkTargetForCmd($cmd)
	{
		/** @var $ilCtrl ilCtrl */
		/** @var $ilIliasIniFile ilIniFile */
		global $ilCtrl, $ilIliasIniFile;
		return $ilIliasIniFile->readVariable('server', 'http_path') . '/' . $ilCtrl->getLinkTarget($this->getGUIObject(), $cmd);
	}

	/**
	 * @param string $cmd
	 * @param string $ressource
	 *
	 * @return string
	 */
	protected function getLinkTargetForRessource($cmd, $ressource)
	{
		/** @var $ilCtrl ilCtrl */
		/** @var $ilIliasIniFile ilIniFile */
		global $ilCtrl, $ilIliasIniFile;
		$link = $ilIliasIniFile->readVariable('server', 'http_path') . '/' 
			. $ilCtrl->getLinkTarget($this->getGUIObject(), $cmd) . '&ressource=' . $ressource;
		return $link;
	}

	/**
	 * @param string $default_cmd
	 *
	 * @return string
	 */
	protected function getFormAction($default_cmd)
	{
		/** @var $ilCtrl ilCtrl */
		global $ilCtrl;
		return $ilCtrl->getFormAction($this, $default_cmd);
	}

	/**
	 * Passes the control to the plugin.
	 *
	 * @param string|null $cmd Optional command for the plugin
	 *
	 * @return void
	 */
	abstract function invoke($cmd = null);
	
	
}