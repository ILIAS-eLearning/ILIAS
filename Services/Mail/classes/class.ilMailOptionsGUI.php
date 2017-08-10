<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Mail/classes/class.ilMailbox.php";
require_once "Services/Mail/classes/class.ilFormatMail.php";
require_once 'Services/Mail/classes/class.ilMailOptions.php';
require_once 'Services/Mail/classes/class.ilMailOptionsFormGUI.php';

/**
* @author Jens Conze
* @version $Id$
*
* @ingroup ServicesMail
*/
class ilMailOptionsGUI
{
	/**
	 * @var \ilTemplate
	 */
	private $tpl;

	/**
	 * @var \ilCtrl
	 */
	private $ctrl;

	/**
	 * @var \ilLanguage
	 */
	private $lng;

	/**
	 * @var \ilSetting
	 */
	private $settings;

	/**
	 * @var \ilObjUser
	 */
	private $user;

	/**
	 * @var \ilFormatMail
	 */
	private $umail;

	/**
	 * @var ilMailBox|null
	 */
	private $mbox;

	/**
	 * ilMailOptionsGUI constructor.
	 */
	public function __construct()
	{
		global $DIC;

		$this->tpl      = $DIC->ui()->mainTemplate();
		$this->ctrl     = $DIC->ctrl();
		$this->settings = $DIC->settings();
		$this->lng      = $DIC->language();
		$this->user     = $DIC->user();

		$this->lng->loadLanguageModule('mail');

		$this->ctrl->saveParameter($this, 'mobj_id');

		$this->umail = new ilFormatMail($this->user->getId());
		$this->mbox  = new ilMailBox($this->user->getId());
	}

	public function executeCommand()
	{
		$nextClass = $this->ctrl->getNextClass($this);
		switch($nextClass)
		{
			default:
				if(!($cmd = $this->ctrl->getCmd()))
				{
					$cmd = 'showOptions';
				}

				$this->$cmd();
				break;
		}
	}

	/**
	 * @return ilMailOptionsFormGUI
	 */
	protected function getForm()
	{
		return new ilMailOptionsFormGUI(
			new ilMailOptions($this->user->getId()),
			$this, 'saveOptions'
		);
	}

	/** 
	 * Called if the user pushes the submit button of the mail options form.
	 * Passes the post data to the mail options model instance to store them.
	 */
	public function saveOptions()
	{
		$this->tpl->setTitle($this->lng->txt('mail'));

		$form = $this->getForm();
		if($form->save())
		{
			ilUtil::sendSuccess($this->lng->txt('mail_options_saved'));
		}

		$this->showOptions($form);
	}

	/** 
	 * Called to display the mail options form
	 * @param $form ilMailOptionsFormGUI|null
	 */
	public function showOptions(ilMailOptionsFormGUI $form = null)
	{
		if(null === $form)
		{
			$form = $this->getForm();
			$form->populate();
		}
		else
		{
			$form->setValuesByPost();
		}

		$this->tpl->setContent($form->getHTML());
		$this->tpl->show();
	}
}
