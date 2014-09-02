<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Password/classes/class.ilBasePasswordEncoder.php';
require_once 'Services/Password/interfaces/interface.ilPasswordEncoderConfigurationFormAware.php';

/**
 * Class ilBcryptPasswordEncoder
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilBcryptPasswordEncoder extends ilBasePasswordEncoder implements ilPasswordEncoderConfigurationFormAware
{
	/**
	 * @var int
	 */
	const MIN_SALT_SIZE         = 16;

	/**
	 * @var string
	 */
	const SALT_STORAGE_FILENAME = 'pwsalt.txt';

	/**
	 * @var string|null
	 */
	protected $client_salt = null;

	/**
	 * @var string
	 */
	protected $costs = '08';

	/**
	 * @var bool
	 */
	protected $is_security_flaw_ignored = false;

	/**
	 * @var bool
	 */
	protected $backward_compatibility = false;

	/**
	 * @param array $config
	 * @throws ilPasswordException
	 */
	public function __construct(array $config = array())
	{
		if(!empty($config))
		{
			foreach($config as $key => $value)
			{
				switch(strtolower($key))
				{
					case 'cost':
						$this->setCosts($value);
						break;

					case 'ignore_security_flaw':
						$this->setIsSecurityFlawIgnored($value);
						break;
				}
			}
		}
		
		$this->init();
	}

	/**
	 * 
	 */
	protected function init()
	{
		$this->readClientSalt();
	}

	/**
	 * @return bool
	 */
	protected function isBcryptSupported()
	{
		return PHP_VERSION_ID >= 50307;
	}

	/**
	 * @return boolean
	 */
	public function isBackwardCompatibilityEnabled()
	{
		return (bool)$this->backward_compatibility;
	}

	/**
	 * Set the backward compatibility $2a$ instead of $2y$ for PHP 5.3.7+
	 * @param boolean $backward_compatibility
	 */
	public function setBackwardCompatibility($backward_compatibility)
	{
		$this->backward_compatibility = (bool)$backward_compatibility;
	}

	/**
	 * @return boolean
	 */
	public function isSecurityFlawIgnored()
	{
		return (bool)$this->is_security_flaw_ignored;
	}

	/**
	 * @param boolean $is_security_flaw_ignored
	 */
	public function setIsSecurityFlawIgnored($is_security_flaw_ignored)
	{
		$this->is_security_flaw_ignored = (bool)$is_security_flaw_ignored;
	}

	/**
	 * @return string|null
	 */
	public function getClientSalt()
	{
		return $this->client_salt;
	}

	/**
	 * @param string|null $client_salt
	 */
	public function setClientSalt($client_salt)
	{
		$this->client_salt = $client_salt;
	}

	/**
	 * @return string
	 */
	public function getCosts()
	{
		return $this->costs;
	}

	/**
	 * @param string $costs
	 * @throws ilPasswordException
	 */
	public function setCosts($costs)
	{
		if(!empty($costs))
		{
			$costs = (int)$costs;
			if($costs < 4 || $costs > 31)
			{
				require_once 'Services/Password/exceptions/class.ilPasswordException.php';
				throw new ilPasswordException('The costs parameter of bcrypt must be in range 04-31');
			}
			$this->costs = sprintf('%1$02d', $costs);
		}
	}

	/**
	 * {@inheritdoc}
	 * @throws ilPasswordException
	 */
	public function encodePassword($raw, $salt)
	{
		if(!$this->getClientSalt())
		{
			require_once 'Services/Password/exceptions/class.ilPasswordException.php';
			throw new ilPasswordException('Missing client salt.');
		}

		if($this->isPasswordTooLong($raw))
		{
			require_once 'Services/Password/exceptions/class.ilPasswordException.php';
			throw new ilPasswordException('Invalid password.');
		}
		
		return $this->encode($raw, $salt);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isPasswordValid($encoded, $raw, $salt)
	{
		if(!$this->getClientSalt())
		{
			require_once 'Services/Password/exceptions/class.ilPasswordException.php';
			throw new ilPasswordException('Missing client salt.');
		}

		return !$this->isPasswordTooLong($raw)  && $this->check($encoded, $raw, $salt);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'bcrypt';
	}

	/**
	 * {@inheritdoc}
	 */
	public function requiresSalt()
	{
		return true;
	}

	/**
	 * Generates a bcrypt encoded string
	 * @param    string $raw
	 * @param    string $salt
	 * @return   string
	 * @throws   ilPasswordException
	 */
	protected function encode($raw, $salt)
	{
		$hashed_password = hash_hmac('whirlpool', str_pad($raw, strlen($raw) * 4, sha1($salt), STR_PAD_BOTH), $this->getClientSalt(), true);
		$salt            = substr(str_shuffle(str_repeat('./0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 22)), 0, 22);

		/**
		 * Check for security flaw in the bcrypt implementation used by crypt()
		 * @see http://php.net/security/crypt_blowfish.php
		 */
		if($this->isBcryptSupported() && !$this->isBackwardCompatibilityEnabled())
		{
			$prefix = '$2y$';
		}
		else
		{
			$prefix = '$2a$';
			// check if the password contains 8-bit character
			if(!$this->isSecurityFlawIgnored() && preg_match('/[\x80-\xFF]/', $raw))
			{
				require_once 'Services/Password/exceptions/class.ilPasswordException.php';
				throw new ilPasswordException(
					'The bcrypt implementation used by PHP can contain a security flaw ' .
					'using passwords with 8-bit characters. ' .
					'We suggest to upgrade to PHP 5.3.7+ or use passwords with only 7-bit characters.'
				);
			}
		}

		$encrypted_password = crypt($hashed_password, $prefix . $this->getCosts() . '$' . $salt);
		if(strlen($encrypted_password) <= 13)
		{
			require_once 'Services/Password/exceptions/class.ilPasswordException.php';
			throw new ilPasswordException('Error during the bcrypt generation');
		}
		return $encrypted_password;
	}

	/**
	 * Verifies a bcrypt encoded string
	 * @param    string $encoded
	 * @param    string $raw
	 * @param    string $salt
	 * @return   bool
	 */
	protected function check($encoded, $raw, $salt)
	{
		$hashed_password  = hash_hmac('whirlpool', str_pad($raw, strlen($raw) * 4, sha1($salt), STR_PAD_BOTH), $this->getClientSalt(), true);
		return crypt($hashed_password, substr($encoded, 0, 30)) == $encoded;
	}

	/**
	 * @return string
	 */
	public function getClientSaltLocation()
	{
		return ilUtil::getDataDir() . '/' . self::SALT_STORAGE_FILENAME;
	}

	/**
	 * 
	 */
	private function generateClientSalt()
	{
		require_once 'Services/Password/classes/class.ilPasswordUtils.php';
		$this->setClientSalt(
			substr(str_replace('+', '.', base64_encode(ilPasswordUtils::getBytes(self::MIN_SALT_SIZE))), 0, 22)
		);
	}

	/**
	 * 
	 */
	private function readClientSalt()
	{
		if(is_file($this->getClientSaltLocation()) && is_readable($this->getClientSaltLocation()))
		{
			$contents = file_get_contents($this->getClientSaltLocation());
			if(strlen(trim($contents)))
			{
				$this->setClientSalt($contents);
			}
		}
	}

	/**
	 * @throws ilPasswordException
	 */
	private function storeClientSalt()
	{
		$result = @file_put_contents($this->getClientSaltLocation(), $this->getClientSalt());
		if(!$result)
		{
			throw new ilPasswordException("Could not store the client salt. Please contact an administrator.");
		}
	}

	/**
	 * {@inheritdoc}
	 * @throws ilPasswordException
	 */
	public function onSelection()
	{
		if(!$this->getClientSalt())
		{
			try
			{
				$this->generateClientSalt();
				$this->storeClientSalt();
			}
			catch(ilPasswordException $e)
			{
				$this->setClientSalt(null);
				throw $e;
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateForm(ilPropertyFormGUI $form)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		if(!strlen(trim($this->getClientSalt())) || !preg_match('/^.{' . self::MIN_SALT_SIZE . ',}$/', $this->getClientSalt()))
		{
			$form->getItemByPostVar('bcrypt_salt')->setAlert($lng->txt('passwd_encoder_bcrypt_client_salt_invalid'));
			return false;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function saveForm(ilPropertyFormGUI $form)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(ilPropertyFormGUI $form)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($lng->txt('passwd_encoder_' . $this->getName()));
		$form->addItem($header);

		$salt = new ilCustomInputGUI($lng->txt('passwd_encoder_bcrypt_client_salt'), 'bcrypt_salt');

		$info = array($lng->txt('passwd_encoder_client_bcrypt_salt_info'));
		if(!$this->isBcryptSupported())
		{
			$info[] = sprintf($lng->txt('passwd_encoder_client_bcrypt_salt_info_php537'), PHP_VERSION);
		}
		if(1 == count($info))
		{
			$salt->setInfo(current($info));
		}
		else
		{
			$salt->setInfo('<ul><li>' . implode('</li><li>', $info) . '</li></ul>');
		}

		$salt->setHtml($this->getClientSaltLocation());

		$form->addItem($salt);
	}
}
