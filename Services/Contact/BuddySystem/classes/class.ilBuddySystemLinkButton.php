<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/JSON/classes/class.ilJsonUtil.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';
require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystemGUI.php';
require_once 'Services/Contact/BuddySystem/classes/states/class.ilBuddySystemRelationStateFactory.php';
require_once 'Services/Contact/BuddySystem/interfaces/interface.ilBuddySystemLinkButtonType.php';

/**
 * Class ilBuddySystemLinkButton
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemLinkButton implements ilBuddySystemLinkButtonType
{
	/**
	 * @var int
	 */
	protected $usr_id;

	/**
	 * @var ilBuddyList
	 */
	protected $buddylist;
	
	/**
	 * @param $usr_id
	 */
	protected function __construct($usr_id)
	{
		$this->usr_id    = $usr_id;
		$this->buddylist = ilBuddyList::getInstanceByGlobalUser();
	}

	/**
	 * @param int $usr_id
	 * @return ilBuddySystemLinkButton
	 */
	public static function getInstanceByUserId($usr_id)
	{
		return new self($usr_id);
	}

	/**
	 * @return int
	 */
	public function getUsrId()
	{
		return $this->usr_id;
	}

	/**
	 * @param int $usr_id
	 */
	public function setUsrId($usr_id)
	{
		$this->usr_id = $usr_id;
	}

	/**
	 * @return ilBuddyList
	 */
	public function getBuddyList()
	{
		return $this->buddylist;
	}

	/**
	 * @return string
	 */
	public function getHtml()
	{
		/**
		 * @var $lng    ilLanguage
		 * @var $ilUser ilObjUser
		 */
		global $lng, $ilUser;

		$lng->loadLanguageModule('buddysystem');

		ilBuddySystemGUI::initializeFrontend();

		require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystem.php';
		if(!ilBuddySystem::getInstance()->isEnabled())
		{
			return '';
		}

		$relation = $this->buddylist->getRelationByUserId($this->getUsrId());

		// The ILIAS JF decided to add a new personal setting
		if($relation->isUnlinked() && !ilUtil::yn2tf(ilObjUser::_lookupPref($this->getUsrId(), 'bs_allow_to_contact_me')))
		{
			return '';
		}

		$button_tpl = new ilTemplate('tpl.buddy_system_link_button.html', true, true, 'Services/Contact/BuddySystem');
		$button_tpl->setVariable('BUTTON_HTML', ilBuddySystemRelationStateFactory::getInstance()->getRendererByOwnerAndRelation($ilUser->getId(), $relation)->getHtml());
		$button_tpl->setVariable('BUTTON_BUDDY_ID', $this->getUsrId());
		$button_tpl->setVariable('BUTTON_CSS_CLASS', 'ilBuddySystemLinkWidget');
		$button_tpl->setVariable('BUTTON_CURRENT_STATE', get_class($relation->getState()));
		return $button_tpl->get();
	}
}