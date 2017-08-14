<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentials.php';
require_once 'Services/Authentication/interfaces/interface.ilAuthCredentials.php';

/**
 * Class ilAuthFrontendCredentialsSaml
 */
class ilAuthFrontendCredentialsSaml extends ilAuthFrontendCredentials implements ilAuthCredentials
{
	/**
	 * @var array
	 */
	protected static $_requestAttributes = array();

	/**
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * @var string
	 */
	protected $return_to = '';

	/**
	 * ilAuthFrontendCredentialsSaml constructor.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Init credentials from request
	 */
	public function initFromRequest()
	{
		$this->setUsername('dummy');
		$this->setPassword('');

		$this->setAttributes(self::$_requestAttributes);
		$this->setReturnTo(isset($_GET['target']) ? $_GET['target'] : '');
	}

	/**
	 * @return array
	 */
	public static function getRequestAttributes()
	{
		return self::$_requestAttributes;
	}

	/**
	 * @param array $requestAttributes
	 */
	public static function setRequestAttributes($requestAttributes)
	{
		self::$_requestAttributes = $requestAttributes;
	}

	/**
	 * @param array $attributes
	 */
	public function setAttributes(array $attributes)
	{
		$this->attributes = $attributes;
	}

	/**
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * @return string
	 */
	public function getReturnTo()
	{
		return $this->return_to;
	}

	/**
	 * @param string $return_to
	 */
	public function setReturnTo($return_to)
	{
		$this->return_to = $return_to;
	}
}