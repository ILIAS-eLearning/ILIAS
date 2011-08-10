<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author jposselt
 */
abstract class ilDBayTaskHandler
{

	abstract public function __construct(ilDBayObjectGUI $gui);

	abstract public function executeDefault($requestedMethod);

	/**
	 * Executes given $method if existing, otherwise executes
	 * executeDefault() method.
	 *
	 * @param string $method
	 * @return mixed
	 */
	public function execute($method)
	{
		global $lng;
		
		$lng->loadLanguageModule('chatroom');

		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
		
		if( method_exists( $this, $method ) )
		{
			return $this->$method();
		}
		else
		{
			return $this->executeDefault( $method );
		}
	}

}

?>