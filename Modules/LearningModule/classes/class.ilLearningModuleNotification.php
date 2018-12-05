<?php
/* Copyright (c) 2018 Extended GPL, see docs/LICENSE */

/**
 * Class ilLearningModuleNotification class
 *
 * //TODO create an interface for notifications contract.(ilnotification?).Similar code in ilwikipage, ilblogposting
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesIliasLearningModule
 */
class ilLearningModuleNotification
{
	/**
	 * @var ilObjUser
	 */
	protected $ilUser;

	/**
	 * @var ilAccessHandler
	 */
	protected $ilAccess;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilSetting
	 */
	protected $lm_set;

	/**
	 * @var string
	 */
	protected $action;

	/**
	 * @var int
	 */
	protected $type;

	/**
	 * @var ilObjLearningModule
	 */
	protected $learning_module;

	/**
	 * @var int
	 */
	protected $page_id;

	/**
	 * @var string
	 */
	protected $comment;

	/**
	 * ilLearningModuleNotification constructor.
	 * @param string $a_action
	 * @param int $a_type
	 * @param ilObjLearningModule $a_learning_module
	 * @param int $a_page_id
	 * @param string|null $a_comment
	 */
	public function __construct(string $a_action, int $a_type, ilObjLearningModule $a_learning_module, int $a_page_id, string $a_comment = null)
	{
		global $DIC;

		$this->ilUser = $DIC->user();
		$this->ilAccess = $DIC->access();
		$this->lng = $DIC->language();
		$this->lng->loadLanguageModule("content");
		$this->lm_set = new ilSetting("lm");
		$this->action = $a_action;
		$this->type = $a_type;
		$this->learning_module = $a_learning_module;
		$this->page_id = $a_page_id;
		$this->comment = $a_comment;
	}

	protected function send()
	{
		//currently only notifications from new comments are implemented
		if($this->action != "comment")
		{
			return;
		}

		$lm_id = $this->learning_module->getId();
		$lm_ref_id = $this->learning_module->getRefId();

		$pg_title = ilLMPageObject::_getPresentationTitle($this->page_id,
			$this->learning_module->getPageHeader(), $this->learning_module->isActiveNumbering(),
			$this->lm_set->get("time_scheduled_page_activation"), false, 0, $this->lng);

		// #11138  //only comment implemented so always true.
		$ignore_threshold = ($this->action == "comment");

		$users = ilNotification::getNotificationsForObject(ilNotification::TYPE_LM, $lm_id, $this->page_id, $ignore_threshold);

		if ($this->type == ilNotification::TYPE_LM_PAGE)
		{
			$page_users = ilNotification::getNotificationsForObject($$this->type, $this->page_id, null, $ignore_threshold);
			$users = array_merge($users, $page_users);
		}
		if(!sizeof($users))
		{
			return;
		}

		ilNotification::updateNotificationTime(ilNotification::TYPE_LM, $lm_id, $users, $this->page_id);

		// #15192 - should always be present
		if($this->page_id)
		{
			// #18804 - see ilWikiPageGUI::preview()
			$link = ilLink::_getLink("", "pg", null, $this->page_id."_".$lm_ref_id);
		}
		else
		{
			$link = ilLink::_getLink($lm_ref_id);
		}

		foreach(array_unique($users) as $idx => $user_id)
		{
			if($user_id != $this->ilUser->getId() &&
				$this->ilAccess->checkAccessOfUser($user_id, 'read', '', $lm_ref_id))
			{
				// use language of recipient to compose message
				$ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
				$ulng->loadLanguageModule('content');

				$subject = sprintf($ulng->txt('cont_notification_comment_subject'), $this->learning_module->getTitle(), $pg_title);
				$message = sprintf($ulng->txt('cont_change_notification_salutation'), ilObjUser::_lookupFullname($user_id))."\n\n";

				$message .= $ulng->txt('cont_notification_'.$this->action).":\n\n";
				$message .= $ulng->txt('learning module').": ".$this->learning_module->getTitle()."\n";
				$message .= $ulng->txt('page').": ".$pg_title."\n";
				$message .= $ulng->txt('cont_commented_by').": ".ilUserUtil::getNamePresentation($this->ilUser->getId())."\n";

				// include comment/note text
				$message .= "\n".$ulng->txt('comment').":\n\"".trim($this->comment)."\"\n";

				$message .= "\n".$ulng->txt('url').": ".$link;

				$mail_obj = new ilMail(ANONYMOUS_USER_ID);
				$mail_obj->appendInstallationSignature(true);
				$mail_obj->sendMail(ilObjUser::_lookupLogin($user_id),
					"", "", $subject, $message, array(), array("system"));
			}
		}
	}
}