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
	const STORAGE_ID = 'oidc';

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
	 * ilOpenIdConnectSettings constructor.
	 */
	private function __construct()
	{
		$this->storage = new ilSetting(self::STORAGE_ID);
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
	 * Save in settings
	 */
	public function save()
	{
		$this->storage->set('active', (int) $this->getActive());
		$this->storage->set('provider', $this->getProvider());
		$this->storage->set('client_id', $this->getClientId());
	}

	/**
	 * Load from settings
	 */
	protected function load()
	{
		$this->setActive((bool) $this->storage->get('active', 0));
		$this->setProvider($this->storage->get('provider', ''));
		$this->setClientId($this->storage->get('client_id',''));
	}


}
