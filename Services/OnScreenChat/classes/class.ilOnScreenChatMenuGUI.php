<?php

/**
 * Class ilOnScreenChatMenuGUI
 *
 * @author  Thomas Joußen <tjoussen@databay.de>
 * @since   03.08.16
 */
class ilOnScreenChatMenuGUI
{

	public function initialize()
	{
		global $DIC;

		require_once 'Services/JSON/classes/class.ilJsonUtil.php';

		$config = array(
			'conversationTemplate' => file_get_contents('./Services/OnScreenChat/templates/default/tpl.chat-menu-item.html'),
			'userId' => $DIC->user()->getId()
		);

		$DIC->language()->loadLanguageModule('chatroom');
		$DIC->language()->toJS(array(
			'chat_osc_conversations', 'chat_osc_sure_to_leave_grp_conv', 'chat_osc_user_left_grp_conv',
			'confirm', 'cancel', 'chat_osc_leave_grp_conv'
		));

		$DIC['tpl']->addJavascript('./Services/OnScreenChat/js/onscreenchat-menu.js');
		$DIC['tpl']->addJavascript('./Services/UIComponent/Modal/js/Modal.js');
		$DIC['tpl']->addOnLoadCode("il.OnScreenChatMenu.setConfig(".ilJsonUtil::encode($config).");");
		$DIC['tpl']->addOnLoadCode("il.OnScreenChatMenu.init();");

	}
	public function getMainMenuHTML()
	{
		$tpl = new ilTemplate('tpl.chat-menu.html', false, false, 'Services/OnScreenChat');
		$tpl->setVariable("LOADER", ilUtil::getImagePath("loader.svg"));

		return $tpl->get();
	}
}
