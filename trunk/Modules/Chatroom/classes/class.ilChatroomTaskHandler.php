<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author jposselt@databay.de
 */
abstract class ilChatroomTaskHandler
{
	/**
	 * @param ilChatroomObjectGUI $gui
	 */
	abstract public function __construct(ilChatroomObjectGUI $gui);

	/**
	 * @param string $requestedMethod
	 * @return mixed
	 */
	abstract public function executeDefault($requestedMethod);

	/**
	 * Executes given $method if existing, otherwise executes
	 * executeDefault() method.
	 * @param string $method
	 * @return mixed
	 */
	public function execute($method)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$lng->loadLanguageModule('chatroom');

		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';

		if(method_exists($this, $method))
		{
			return $this->$method();
		}
		else
		{
			return $this->executeDefault($method);
		}
	}
}