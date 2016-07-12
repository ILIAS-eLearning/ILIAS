<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/interfaces/interface.ilAuthCredentials.php';

/**
 * Description of class class 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilAuthFrontendCredentials implements ilAuthCredentials
{
	private $password = '';
	private $username = '';
	private $captcha = '';
	private $auth_mode = '';
	
	public function __construct()
	{
		
	}

	/**
	 * Get password
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * Get username
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * Set password
	 * @param string $a_password
	 */
	public function setPassword($a_password)
	{
		$this->password = $a_password;
	}

	/**
	 * Set username
	 * @param string
	 */
	public function setUsername($a_name)
	{
		$this->username = $a_name;
	}
	
	/**
	 * Set captcha code
	 * @param string
	 */
	public function setCaptchaCode($a_code)
	{
		$this->captcha = $a_code;
	}

}
?>