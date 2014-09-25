<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/exceptions/class.ilUserException.php';

/**
 * Class ilUserPasswordManager
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesUser
 */
class ilUserPasswordManager
{
	/**
	 * @var int
	 */
	const MIN_SALT_SIZE = 16;

	/**
	 * @var self
	 */
	private static $instance;

	/**
	 * @var ilUserPasswordEncoderFactory
	 */
	protected $encoder_factory;

	/**
	 * @var string
	 */
	protected $encoder_name;

	/**
	 * @var array
	 */
	protected $config = array();

	/**
	 * Please use the singleton method for instance creation
	 * The constructor is still public because of the unit tests
	 * @param array $config
	 * @throws ilUserException
	 */
	public function __construct(array $config = array())
	{
		if(!empty($config))
		{
			foreach($config as $key => $value)
			{
				switch(strtolower($key))
				{
					case 'password_encoder':
						$this->setEncoderName($value);
						break;
					case 'encoder_factory':
						$this->setEncoderFactory($value);
						break;
				}
			}
		}

		if(!$this->getEncoderName())
		{
			throw new ilUserException(sprintf('"password_encoder" must be set in %s.', json_encode($config)));
		}

		if(!($this->getEncoderFactory() instanceof ilUserPasswordEncoderFactory))
		{
			throw new ilUserException(sprintf('"encoder_factory" must be instance of ilUserPasswordEncoderFactory and set in %s.', json_encode($config)));
		}
	}

	/**
	 * Single method to reduce footprint (included files, created instances)
	 * @return self
	 */
	public static function getInstance()
	{
		if(self::$instance instanceof self)
		{
			return self::$instance;
		}

		/**
		 * @var $ilClientIniFile ilIniFile
		 */
		global $ilClientIniFile;

		require_once 'Services/User/classes/class.ilUserPasswordEncoderFactory.php';
		$password_manager = new ilUserPasswordManager(
			array(
				'encoder_factory' => new ilUserPasswordEncoderFactory(
					array(
						'default_password_encoder' => 'md5',
						'ignore_security_flaw'     => true
					)
				),
				'password_encoder' =>
					$ilClientIniFile->readVariable('auth', 'password_encoder') ?
					$ilClientIniFile->readVariable('auth', 'password_encoder') :
					'md5',
			)
		);

		self::$instance = $password_manager;
		return self::$instance;
	}

	/**
	 * @return string
	 */
	public function getEncoderName()
	{
		return $this->encoder_name;
	}

	/**
	 * @param string $encoder_name
	 */
	public function setEncoderName($encoder_name)
	{
		$this->encoder_name = $encoder_name;
	}

	/**
	 * @return ilUserPasswordEncoderFactory
	 */
	public function getEncoderFactory()
	{
		return $this->encoder_factory;
	}

	/**
	 * @param ilUserPasswordEncoderFactory $encoder_factory
	 */
	public function setEncoderFactory(ilUserPasswordEncoderFactory $encoder_factory)
	{
		$this->encoder_factory = $encoder_factory;
	}
	
	/**
	 * @param ilObjUser $user
	 * @param string $raw The raw password
	 */
	public function encodePassword(ilObjUser $user, $raw)
	{
		$encoder = $this->getEncoderFactory()->getEncoderByName($this->getEncoderName());
		$user->setPasswordEncodingType($encoder->getName());
		if($encoder->requiresSalt())
		{
			require_once 'Services/Password/classes/class.ilPasswordUtils.php';
			$user->setPasswordSalt(
				substr(str_replace('+', '.', base64_encode(ilPasswordUtils::getBytes(self::MIN_SALT_SIZE))), 0, 22)
			);
		}
		else
		{
			$user->setPasswordSalt(null);
		}
		$user->setPasswd($encoder->encodePassword($raw, $user->getPasswordSalt()), IL_PASSWD_CRYPTED);
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function isEncodingTypeSupported($name)
	{
		return in_array($name, $this->getEncoderFactory()->getSupportedEncoderNames());
	}

	/**
	 * @param  ilObjUser $user
	 * @param  string    $raw
	 * @return bool
	 */
	public function verifyPassword(ilObjUser $user, $raw)
	{
		$encoder = $this->getEncoderFactory()->getEncoderByName($user->getPasswordEncodingType(), true);
		if($this->getEncoderName() != $encoder->getName())
		{
			if($encoder->isPasswordValid($user->getPasswd(), $raw, $user->getPasswordSalt()))
			{
				$user->resetPassword($raw, $raw);
				return true;
			}

			return false;
		}
		else
		{
			return $encoder->isPasswordValid($user->getPasswd(), $raw, $user->getPasswordSalt());
		}
	}
} 