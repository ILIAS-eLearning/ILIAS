<?php

/**
 * Class ilOnScreenChatMenuGUI
 *
 * @author  Thomas Joußen <tjoussen@databay.de>
 * @since   03.08.16
 */
class ilOnScreenChatMenuGUI
{
	/**
	 * @var integer
	 */
	protected $pub_ref_id;

	/**
	 * @var bool
	 */
	protected $accessible = false;

	/**
	 * @var bool
	 */
	protected $publicChatRoomAccess = false;

	/**
	 * @var bool
	 */
	protected $oscAccess = false;

	/**
	 * ilOnScreenChatMenuGUI constructor.
	 */
	public function __construct()
	{
		$this->init();
	}

	/**
	 * @return bool
	 */
	protected function init()
	{
		global $DIC;

		require_once 'Modules/Chatroom/classes/class.ilObjChatroom.php';
		$this->pub_ref_id = ilObjChatroom::_getPublicRefId();

		if(!$DIC->user() || $DIC->user()->isAnonymous())
		{
			$this->accessible = false;
			return;
		}

		$chatSettings = new ilSetting('chatroom');

		$this->publicChatRoomAccess = $DIC->rbac()->system()->checkAccessOfUser($DIC->user()->getId(), 'read', $this->pub_ref_id);
		$this->oscAccess            = $chatSettings->get('enable_osc');

		$this->accessible = $chatSettings->get('chat_enabled') && ($this->oscAccess || $this->publicChatRoomAccess);
	}

	/**
	 * @return string
	 */
	public function getMainMenuHTML()
	{
		global $DIC;

		if(!$this->accessible)
		{
			return '';
		}

		require_once 'Services/Link/classes/class.ilLinkifyUtil.php';
		ilLinkifyUtil::initLinkify();

		require_once 'Services/JSON/classes/class.ilJsonUtil.php';
		$DIC->language()->loadLanguageModule('chatroom');

		$config = array(
			'conversationTemplate' => (new ilTemplate('tpl.chat-menu-item.html', false, false, 'Services/OnScreenChat'))->get(),
			'roomTemplate'         => (new ilTemplate('tpl.chat-menu-item-room.html', false, false, 'Services/OnScreenChat'))->get(),
			'userId'               => $DIC->user()->getId()
		);

		$config['rooms'] = array();

		if($this->publicChatRoomAccess)
		{
			$config['rooms'][] = array(
				'name' => $DIC['ilObjDataCache']->lookupTitle($DIC['ilObjDataCache']->lookupObjId($this->pub_ref_id)),
				'url'  => './ilias.php?baseClass=ilRepositoryGUI&amp;cmd=view&amp;ref_id=' . $this->pub_ref_id,
				'icon' => ilObject::_getIcon($DIC['ilObjDataCache']->lookupObjId($this->pub_ref_id), 'small', 'chtr')
			);
		}

		$config['showOnScreenChat'] = $this->oscAccess;

		$DIC->language()->loadLanguageModule('chatroom');
		$DIC->language()->toJS(array(
			'chat_osc_conversations', 'chat_osc_section_head_other_rooms'
		));

		$DIC['tpl']->addJavascript('./Services/OnScreenChat/js/onscreenchat-menu.js');
		$DIC['tpl']->addJavascript('./Services/UIComponent/Modal/js/Modal.js');
		$DIC['tpl']->addOnLoadCode("il.OnScreenChatMenu.setConfig(".ilJsonUtil::encode($config).");");
		$DIC['tpl']->addOnLoadCode("il.OnScreenChatMenu.init();");

		$tpl = new ilTemplate('tpl.chat-menu.html', false, false, 'Services/OnScreenChat');
		$tpl->setVariable("LOADER", ilUtil::getImagePath("loader.svg"));
		return $tpl->get();
	}
}
