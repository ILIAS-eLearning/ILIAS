<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/JSON/classes/class.ilJsonUtil.php';
require_once 'Services/Contact/BuddySystem/exceptions/class.ilBuddySystemException.php';

/**
 * Class ilBuddySystemGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ilCtrl_isCalledBy ilBuddySystemGUI: ilUIPluginRouterGUI
 */
class ilBuddySystemGUI
{
	/**
	 * @var bool
	 */
	protected static $frontend_initialized = false;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilBuddyList
	 */
	protected $buddylist;

	/**
	 * @var ilBuddySystemRelationStateFactory
	 */
	protected $statefactory;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * 
	 */
	public function __construct()
	{
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $ilUser ilObjUser
		 * @var $lng    ilLanguage
		 */
		global $ilCtrl, $ilUser, $lng;

		$this->ctrl = $ilCtrl;
		$this->user = $ilUser;

		require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';
		require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateFactory.php';
		$this->buddylist     = ilBuddyList::getInstanceByGlobalUser();
		$this->statefactory  = ilBuddySystemRelationStateFactory::getInstance();

		$lng->loadLanguageModule('buddysystem');
	}

	/**
	 *
	 */
	public static function handleFrontendInitialization()
	{
		/**
		 * @var $tpl ilTemplate
		 * @var $ilCtrl ilCtrl
		 * @var $lng    ilLanguage
		 */
		global $tpl, $ilCtrl, $lng;

		if(!self::$frontend_initialized)
		{
			$lng->loadLanguageModule('buddysystem');

			require_once 'Services/JSON/classes/class.ilJsonUtil.php';
			$config = new stdClass();
			$config->http_post_url        = $ilCtrl->getFormActionByClass(array('ilUIPluginRouterGUI', 'ilBuddySystemGUI'), '', '', true, false);
			$config->bnt_class            = 'ilBuddySystemLinkWidget';
			$config->transition_state_cmd = 'transition';

			$tpl->addJavascript('./Services/Contact/BuddySystem/js/buddy_system.js');
			$tpl->addOnLoadCode("il.BuddySystemButton.setConfig(".ilJsonUtil::encode($config).");");
			$tpl->addOnLoadCode("il.BuddySystemButton.init();");

			self::$frontend_initialized = true;
		}
	}

	/**
	 * @throws RuntimeException
	 */
	public function executeCommand()
	{
		if(!$this->ctrl->isAsynch())
		{
			throw new RuntimeException('This controller only supports AJAX http requests');
		}

		if($this->user->isAnonymous())
		{
			throw new RuntimeException('This controller only accepts requests of logged in users');
		}

		$next_class = $this->ctrl->getNextClass($this);
		$cmd        = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$cmd .= 'Command';
				$this->$cmd();
				break;
		}
	}

	/**
	 * Performs a state transition based on the request action
	 */
	private function transitionCommand()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		if(!isset($_POST['usr_id']) || !is_numeric($_POST['usr_id']))
		{
			throw new RuntimeException('Missing "usr_id" parameter');
		}

		if(!isset($_POST['action']) || !strlen($_POST['action']))
		{
			throw new RuntimeException('Missing "action" parameter');
		}

		$response = new stdClass();
		$response->success = false;

		try
		{
			$usr_id = (int)$_POST['usr_id'];
			$action = $_POST['action'];

			if(ilObjUser::_isAnonymous($usr_id))
			{
				throw new ilBuddySystemException(sprintf("You cannot perform a state transition for the anonymous user (id: %s)", $usr_id));
			}

			if(!strlen(ilObjUser::_lookupLogin($usr_id)))
			{
				throw new ilBuddySystemException(sprintf("You cannot perform a state transition for a non existing user (id: %s)", $usr_id));
			}

			$relation = $this->buddylist->getRelationByUserId($usr_id);
			try
			{
				$this->buddylist->{$action}($relation);
				$response->success = true;
			}
			catch(Exception $e)
			{
				$response->message = $lng->txt('buddy_bs_action_not_possible');
			}

			$response->state      = get_class($relation->getState());
			$response->state_html = $this->statefactory->getRendererByOwnerAndRelation($this->buddylist->getOwnerId(), $relation)->getHtml();
		}
		catch(Exception $e)
		{
			$response->message = $lng->txt('buddy_bs_action_not_possible');
		}

		echo ilJsonUtil::encode($response);
		exit();
	}

	/**
	 * Delivers a list of requests
	 */
	private function getRelationRequestsCommand()
	{
		$requested_relations = $this->buddylist->getRequestRelationsForOwner();

		require_once 'Services/User/classes/class.ilUserUtil.php';
		require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystemLinkButton.php';

		$response = new stdClass();
		$response->success   = true;
		$response->relations = array();

		$names = ilUserUtil::getNamePresentation($requested_relations->getKeys(), false, false, '', false, true, false, true);
		foreach($requested_relations as $buddy_usr_id => $relation)
		{
			/**
			 * @var $relation ilBuddySystemRelation
			 * @var $user     ilObjUser
			 */
			$user                  = ilObjectFactory::getInstanceByObjId($buddy_usr_id);
			$rel                   = new stdClass();
			$rel->button           = ilBuddySystemLinkButton::getInstanceByUserId($buddy_usr_id)->getHtml();
			$rel->usr_id           = $buddy_usr_id;
			$rel->name             = $names[$buddy_usr_id];
			$rel->ts               = $relation->getTimestamp();
			$rel->img              = $user->getPersonalPicturePath();
			$response->relations[] = $rel;
		}

		echo ilJsonUtil::encode($response);
		exit();
	}
}