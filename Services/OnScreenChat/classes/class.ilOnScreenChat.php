<?php

/**
 * Class ilOnScreenChat
 *
 * @author  Thomas Joußen <tjoussen@databay.de>
 * @since   26.07.16
 */
class ilOnScreenChat
{

	/**
	 * Boolean to track whether this service has already been initialized.
	 *
	 * @var bool
	 */
	protected static $frontend_initialized = false;

	/**
	 * Initialize frontend and delivers required javascript files and configuration to the global template.
	 */
	public static function initializeFrontend()
	{
		global $DIC;

		if(!self::$frontend_initialized)
		{
			require_once 'Services/JSON/classes/class.ilJsonUtil.php';

			$config = array(
				'chatWindowTemplate' => file_get_contents('./Services/OnScreenChat/templates/default/tpl.chat-window.html'),
				'messageTemplate' => file_get_contents('./Services/OnScreenChat/templates/default/tpl.chat-message.html'),
			);

			$DIC->language()->loadLanguageModule('onscreenchat');


			$DIC['tpl']->addCss('./Services/OnScreenChat/templates/default/onscreenchat.css');

			$DIC['tpl']->addJavascript('./Services/OnScreenChat/js/onscreenchat.js');
			$DIC['tpl']->addOnLoadCode("il.OnScreenChat.setConfig(".ilJsonUtil::encode($config).");");
			$DIC['tpl']->addOnLoadCode("il.OnScreenChat.init();");

			self::$frontend_initialized = true;
		}
	}
}
