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
		global $ilCtrl;
		return 
			'//'. $_SERVER['HTTP_HOST'] 
			. substr($_SERVER['PHP_SELF'],0, strlen($_SERVER['PHP_SELF']) - 10) 
			. '/' . $ilCtrl->getLinkTarget($this->getGUIObject(), $cmd, '', false, false);
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
		global $ilCtrl;
		$link = 'http://'. $_SERVER['HTTP_HOST']
			. substr($_SERVER['PHP_SELF'],0, strlen($_SERVER['PHP_SELF']) - 10)
			. '/'
			. $ilCtrl->getLinkTarget($this->getGUIObject(), $cmd, '', false, false) . '&ressource=' . $ressource;
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
	 * Sets the content sections content conveniently. 
	 */
	protected function populatePluginCanvas($content)
	{
		/** @var $tpl ilTemplate */
		global $tpl;
		$tpl->setVariable($this->getGUIObject()->getTestOutputGUI()->getContentBlockName(), $content );
		return;
	}

	/**
	 * Hands in a file from the signature process associated with a given user and pass for archiving. (See docs, pls.)
	 *
	 * Please note: This method checks if archiving is enabled. The test needs to be set to archive data in order
	 * to do something meaningful with the signed files. Still, the plugin will work properly if the signed materials
	 * are not used afterwards. Since the processing in an archive is in fact not the only option to deal with the
	 * files, this possibility of a corrupt settings constellation will not be closed. If your plugin wants to post the
	 * files away to a non-ILIAS-DMS, or the like, you still want to sign files, even if archiving in ILIAS is switched
	 * off.
	 *
	 * @param $active_fi	integer		Active-Id of the user.
	 * @param $pass			integer		Pass-number of the tests submission.
	 * @param $filename		string		Filename that is going to be saved.
	 * @param $filepath		string		Path with the current location of the file.
	 *
	 * @return void
	 */
	protected function handInFileForArchiving($active_fi, $pass, $filename, $filepath)
	{
		if ( $this->getGUIObject()->getTest()->getEnableArchiving() )
		{
			require_once './Modules/Test/classes/class.ilTestArchiver.php';
			$archiver = new ilTestArchiver($this->getGUIObject()->getTest()->getId());
			$archiver->handInParticipantMisc($active_fi, $pass, $filename, $filepath);
		}
	}

	protected function redirectToTest($success)
	{
		$this->getGUIObject()->redirectToTest($success);
	}
	
	/**
	 * Method all commands are forwarded to.
	 * 
	 * This splits the control flow between the ilTestSignatureGUI, which is the command-class at the end of the 
	 * command-forwarding process, and the actual command-execution-class, which is the plugin instance. The plugin will
	 * be called with an eventual command as parameter on this invoke-method and ... makes sense out of it. Whatever
	 * that will be.
	 *
	 * What you see here is called "The Arab Pattern". You will agree, that "Command-Class-Execution-Separation" would
	 * have be to bulky as a name.
	 *
	 * @param mixed|null $cmd Optional command for the plugin
	 *
	 * @return void
	 */
	abstract function invoke($cmd = null);

}