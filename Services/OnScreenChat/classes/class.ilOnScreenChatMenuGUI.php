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
