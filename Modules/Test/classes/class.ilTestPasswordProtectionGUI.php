<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 * 
 * @ilCtrl_Calls ilTestPasswordProtectionGUI: ilPropertyFormGUI
 */
class ilTestPasswordProtectionGUI
{
	const CMD_SHOW_PASSWORD_FORM = 'showPasswordForm';
	const CMD_SAVE_ENTERED_PASSWORD = 'saveEnteredPassword';
	const CMD_BACK_TO_INFO_SCREEN = 'backToInfoScreen';

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilTestPlayerAbstractGUI
	 */
	protected $parentGUI;
	
	/**
	 * @var ilTestPasswordChecker
	 */
	protected $passwordChecker;

	/**
	 * @var string
	 */
	private $nextCommandClass;

	/**
	 * @var string
	 */
	private $nextCommandCmd;
	
	public function __construct(ilCtrl $ctrl, ilTemplate $tpl, ilLanguage $lng, ilTestPlayerAbstractGUI $parentGUI, ilTestPasswordChecker $passwordChecker)
	{
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->parentGUI = $parentGUI;
		$this->passwordChecker = $passwordChecker;
	}
	
	public function executeCommand()
	{
		$this->ctrl->saveParameter($this, 'nextCommand');
		$nextCommand = explode('::', $_GET['nextCommand']);
		$this->setNextCommandClass($nextCommand[0]);
		$this->setNextCommandCmd($nextCommand[1]);

		$this->ctrl->saveParameter($this->parentGUI, 'lock');

		switch($this->ctrl->getNextClass())
		{
			default:

				$cmd = $this->ctrl->getCmd().'Cmd';
				$this->$cmd();
		}
	}
	
	private function showPasswordFormCmd()
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		require_once 'Services/Form/classes/class.ilPasswordInputGUI.php';
		
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt("tst_password_form"));
		$form->setDescription($this->lng->txt("tst_password_introduction"));

		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton(self::CMD_BACK_TO_INFO_SCREEN, $this->lng->txt("cancel"));
		$form->addCommandButton(self::CMD_SAVE_ENTERED_PASSWORD, $this->lng->txt("submit"));

		$inp = new ilPasswordInputGUI($this->lng->txt("tst_password"), 'password');
		$inp->setRequired(true);
		$inp->setRetype(false);
		$form->addItem($inp);

		$this->tpl->setVariable($this->parentGUI->getContentBlockName(), $this->ctrl->getHTML($form));
	}
	
	private function saveEnteredPasswordCmd()
	{
		$this->passwordChecker->setUserEnteredPassword($_POST["password"]);
		
		if( !$this->passwordChecker->isUserEnteredPasswordCorrect() )
		{
			ilUtil::sendFailure($this->lng->txt("tst_password_entered_wrong_password"), true);
		}

		$this->ctrl->redirectByClass($this->getNextCommandClass(), $this->getNextCommandCmd());
	}
	
	private function backToInfoScreenCmd()
	{
		$this->ctrl->redirectByClass('ilObjTestGUI', 'infoScreen');
	}

	private function setNextCommandClass($nextCommandClass)
	{
		$this->nextCommandClass = $nextCommandClass;
	}

	private function getNextCommandClass()
	{
		return $this->nextCommandClass;
	}

	private function setNextCommandCmd($nextCommandCmd)
	{
		$this->nextCommandCmd = $nextCommandCmd;
	}

	private function getNextCommandCmd()
	{
		return $this->nextCommandCmd;
	}
} 