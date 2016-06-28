<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Password/classes/class.ilBasePasswordEncoder.php';
require_once 'Services/Password/interfaces/interface.ilPasswordEncoderConfigurationFormAware.php';

/**
 * Class ilBcryptPhpPasswordEncoder
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilBcryptPhpPasswordEncoder extends ilBasePasswordEncoder implements ilPasswordEncoderConfigurationFormAware
{
	/**
	 * @var string
	 */
	protected $costs = '08';

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
	}

	/**
	 * {@inheritdoc}
	 */
	public function isSupportedByRuntime()
	{
		return parent::isSupportedByRuntime() && version_compare(phpversion(), '5.5.0', '>=');
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
		if($this->isPasswordTooLong($raw))
		{
			require_once 'Services/Password/exceptions/class.ilPasswordException.php';
			throw new ilPasswordException('Invalid password.');
		}

		return password_hash($raw, PASSWORD_BCRYPT, array(
			'cost' => $this->getCosts(),
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function isPasswordValid($encoded, $raw, $salt)
	{
		return password_verify($raw, $encoded);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'bcryptphp';
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
		global $DIC;

		$lng = $DIC['lng'];

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
}