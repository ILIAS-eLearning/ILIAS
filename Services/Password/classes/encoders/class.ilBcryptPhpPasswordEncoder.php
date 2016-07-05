<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Password/classes/class.ilBasePasswordEncoder.php';

/**
 * Class ilBcryptPhpPasswordEncoder
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilBcryptPhpPasswordEncoder extends ilBasePasswordEncoder
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
	 * @return string
	 */
	public function getName()
	{
		return 'bcryptphp';
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
			'cost' => $this->getCosts()
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
	public function requiresReencoding($encoded)
	{
		return password_needs_rehash($encoded, PASSWORD_BCRYPT, array(
			'cost' => $this->getCosts()
		));
	}
}