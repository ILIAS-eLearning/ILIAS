<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilOpenIdConnectSettingsGUI
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 *
 */
class ilOpenIdConnectSettings
{
	const FILE_STORAGE = 'openidconnect/login_form_image';
	const STORAGE_ID = 'oidc';

	const LOGIN_ELEMENT_TYPE_TXT = 0;
	const LOGIN_ELEMENT_TYPE_IMG = 1;

	/**
	 * @var \ilOpenIdConnectSettings
	 *
	 */
	private static $instance = null;


	/**
	 * @var \ilSetting
	 */
	private $storage = null;

	/**
	 * @var \ILIAS\Filesystem\
	 */
	private $filesystem = null;


	/**
	 * @var bool
	 */
	private $active = false;

	/**
	 * @var string
	 */
	private $provider = '';

	/**
	 * @var string
	 */
	private $client_id = '';

	/**
	 * @var string
	 */
	private $secret = '';

	/**
	 * @var int
	 */
	private $login_element_type = self::LOGIN_ELEMENT_TYPE_TXT;

	/**
	 * @var string
	 */
	private $login_element_img_name;

	/**
	 * @var string
	 */
	private $login_element_text;

	/**
	 * ilOpenIdConnectSettings constructor.
	 */
	private function __construct()
	{
		global $DIC;

		$this->storage = new ilSetting(self::STORAGE_ID);
		$this->filesystem = $DIC->filesystem()->web();
		$this->load();
	}

	/**
	 * Get singleton instance
	 * @return \ilOpenIdConnectSettings
	 */
	public static function getInstance() : \ilOpenIdConnectSettings
	{
		if(!self::$instance)
		{
			self::$instance = new self();
		}
		return new self::$instance;
	}

	/**
	 * @param bool $active
	 */
	public function setActive(bool $active)
	{
		$this->active = $active;
	}

	/**
	 * @return bool
	 */
	public function getActive() : bool
	{
		return $this->active;
	}

	/**
	 * @param string $url
	 */
	public function setProvider(string $url)
	{
		$this->provider = $url;
	}

	/**
	 * @return string
	 */
	public function getProvider() : string
	{
		return $this->provider;
	}

	/**
	 * @param string $client_id
	 */
	public function setClientId(string $client_id)
	{
		$this->client_id = $client_id;
	}

	/**
	 * @return string
	 */
	public function getClientId() : string
	{
		return $this->client_id;
	}

	/**
	 * @param string $secret
	 */
	public function setSecret(string $secret)
	{
		$this->secret = $secret;
	}

	/**
	 * Get secret
	 */
	public function getSecret() : string
	{
		return $this->secret;
	}

	/**
	 * Set login element type
	 */
	public function setLoginElementType(int $type)
	{
		$this->login_element_type = $type;
	}

	/**
	 * @return int
	 */
	public function getLoginElementType() : int
	{
		return $this->login_element_type;
	}

	/**
	 * @param string $a_img_name
	 */
	public function setLoginElementImage(string $a_img_name)
	{
		$this->login_element_img_name = $a_img_name;
	}

	/**
	 * @return string
	 */
	public function getLoginElementImage() : string
	{
		return $this->login_element_img_name;
	}

	public function setLoginElementText(string $text)
	{
		$this->login_element_text = $text;
	}


	public function getLoginElemenText()
	{
		return $this->login_element_text;
	}

	/**
	 * Delete image file
	 *
	 * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 */
	public function deleteImageFile()
	{
		if($this->filesystem->has(self::FILE_STORAGE.'/'.$this->getLoginElementImage()))
		{
			$this->filesystem->delete(self::FILE_STORAGE.'/'.$this->getLoginElementImage());
		}
	}

	/**
	 * @return bool
	 */
	public function hasImageFile() : bool
	{
		return
			strlen($this->getLoginElementImage()) &&
			$this->filesystem->has(self::FILE_STORAGE.'/'.$this->getLoginElementImage());
	}

	/**
	 * @return string
	 */
	public function getImageFilePath() : string
	{
		return implode('/',
			[
				\ilUtil::getWebspaceDir(),
				self::FILE_STORAGE.'/'.$this->getLoginElementImage()
			]
		);
	}


	/**
	 * Save in settings
	 */
	public function save()
	{
		$this->storage->set('active', (int) $this->getActive());
		$this->storage->set('provider', $this->getProvider());
		$this->storage->set('client_id', $this->getClientId());
		$this->storage->set('secret', $this->getSecret());
		$this->storage->set('le_img', $this->getLoginElementImage());
		$this->storage->set('le_text', $this->getLoginElemenText());
		$this->storage->set('le_type', $this->getLoginElementType());
	}

	/**
	 * Load from settings
	 */
	protected function load()
	{
		$this->setActive((bool) $this->storage->get('active', 0));
		$this->setProvider($this->storage->get('provider', ''));
		$this->setClientId($this->storage->get('client_id',''));
		$this->setSecret($this->storage->get('secret',''));
		$this->setLoginElementImage($this->storage->get('le_img',''));
		$this->setLoginElementText($this->storage->get('le_text'));
		$this->setLoginElementType($this->storage->get('le_type'));
	}
}
