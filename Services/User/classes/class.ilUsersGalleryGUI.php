<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/interfaces/interface.ilGalleryUsers.php';
require_once 'Services/User/classes/class.ilUserUtil.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystem.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystemLinkButton.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystemRelation.php';
require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateFactory.php';
require_once 'Services/Mail/classes/class.ilMailFormCall.php';

/**
 * @ilCtrl_Calls      ilUsersGalleryGUI: ilPublicUserProfileGUI
 * @ilCtrl_isCalledBy ilUsersGalleryGUI: ilObjCourseGUI, ilObjGroupGUI
 */
class ilUsersGalleryGUI
{
	/**
	 * @var ilGalleryUsers
	 */
	protected $object;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var $tpl ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;

	/**
	 * @var array
	 */
	protected $contact_array;

	/**
	 * @param ilGalleryUsers $object
	 */
	public function __construct(ilGalleryUsers $object)
	{
		/**
		 * @var $ilCtrl     ilCtrl
		 * @var $tpl        ilTemplate
		 * @var $lng        ilLanguage
		 * @var $ilUser     ilObjUser
		 * @var $rbacsystem ilRbacSystem
		 */
		global $ilCtrl, $tpl, $lng, $ilUser, $rbacsystem;

		$this->ctrl       = $ilCtrl;
		$this->object     = $object;
		$this->tpl        = $tpl;
		$this->lng        = $lng;
		$this->user       = $ilUser;
		$this->rbacsystem = $rbacsystem;
	}

	/**
	 *
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();
		$cmd        = $this->ctrl->getCmd('view');

		switch($next_class)
		{
			case 'ilpublicuserprofilegui':
				require_once 'Services/User/classes/class.ilPublicUserProfileGUI.php';
				$profile_gui = new ilPublicUserProfileGUI(ilUtil::stripSlashes($_GET['user']));
				$profile_gui->setBackUrl($this->ctrl->getLinkTarget($this, 'view'));
				$this->ctrl->forwardCommand($profile_gui);
				break;

			default:
				switch($cmd)
				{
					default:
						$this->$cmd();
						break;
				}
				break;
		}
	}

	/**
	 * Displays the participants gallery
	 */
	protected function view()
	{
		$template = $this->buildHTML($this->object->getGalleryUsers());
		$this->tpl->setContent($template->get());
	}

	/**
	 * @param ilTemplate $tpl
	 * @param ilObjUser  $user
	 */
	protected function renderLinkButton(ilTemplate $tpl, ilObjUser $user)
	{
		if(
			ilBuddySystem::getInstance()->isEnabled() &&
			$this->user->getId() != $user->getId() &&
			!$this->user->isAnonymous() &&
			!$user->isAnonymous()
		)
		{
			$button = ilBuddySystemLinkButton::getInstanceByUserId($user->getId());
			$tpl->setVariable('BUDDY_HTML', $button->getHtml());
		}
	}

	/**
	 * @param array
	 * @return ilTemplate
	 */
	protected function buildHTML($users)
	{
		$buddylist = ilBuddyList::getInstanceByGlobalUser();
		$tpl       = new ilTemplate('tpl.users_gallery.html', true, true, 'Services/User');

		require_once 'Services/UIComponent/Panel/classes/class.ilPanelGUI.php';
		$panel = ilPanelGUI::getInstance();
		$panel->setBody($this->lng->txt('no_gallery_users_available'));
		$tpl->setVariable('NO_ENTRIES_HTML', json_encode($panel->getHTML()));

		if(!count($users))
		{
			$tpl->setVariable('NO_GALLERY_USERS', $panel->getHTML());
			return $tpl;
		}

		require_once 'Services/UIComponent/Panel/classes/class.ilPanelGUI.php';
		$panel = ilPanelGUI::getInstance();
		$panel->setBody($this->lng->txt('no_gallery_users_available'));
		$tpl->setVariable('NO_ENTRIES_HTML', json_encode($panel->getHTML()));

		foreach($users as $user_data)
		{
			/**
			 * @var $user ilObjUser
			 */
			$user = $user_data['user'];

			if($user_data['public_profile'])
			{
				$tpl->setCurrentBlock('linked_image');
				$this->ctrl->setParameterByClass('ilpublicuserprofilegui', 'user', $user->getId());
				$profile_target = $this->ctrl->getLinkTargetByClass('ilpublicuserprofilegui', 'getHTML');
				$tpl->setVariable('LINK_PROFILE', $profile_target);
				$tpl->setVariable('PUBLIC_NAME', $user_data['public_name']);
			}
			else
			{
				$tpl->setCurrentBlock('unlinked_image');
				$tpl->setVariable('PUBLIC_NAME', $user->getLogin());
			}
			$tpl->setVariable('SRC_USR_IMAGE', $user->getPersonalPicturePath('small'));
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock('user');

			$tpl->setVariable('BUDDYLIST_STATUS', get_class($buddylist->getRelationByUserId($user->getId())->getState()));
			$tpl->setVariable('USER_CC_CLASS', $this->object->getUserCssClass());
			$tpl->setVariable('USER_ID', $user->getId());
			$this->renderLinkButton($tpl, $user);
			$tpl->parseCurrentBlock();
		}

		return $tpl;
	}
}