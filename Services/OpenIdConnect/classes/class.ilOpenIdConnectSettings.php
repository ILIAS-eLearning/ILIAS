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

	const LOGIN_ENFORCE = 0;
	const LOGIN_STANDARD = 1;

	const LOGOUT_SCOPE_GLOBAL = 0;
	const LOGOUT_SCOPE_LOCAL = 1;

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
	 * @var int
	 */
	private $login_prompt_type = self::LOGIN_ENFORCE;


	/**
	 * @var int
	 */
	private $logout_scope;

	/**
	 * @var bool
	 */
	private $custom_session = false;

	/**
	 * @var int
	 */
	private $session_duration = 60;

	/**
	 * @var bool
	 */
	private $allow_sync;

	/**
	 * @var int
	 */
	private $role;

	/**
	 * @var string
	 */
	private $uid = '';

	/**
	 * @var array
	 */
	private $profile_map = [];

	/**
	 * @var array
	 */
	private $profile_update_map = [];

	/**
	 * @var array
	 */
	private $role_mappings = [];


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


	public function getLoginElemenText() : string
	{
		return $this->login_element_text;
	}

	/**
	 * @param int $a_type
	 */
	public function setLoginPromptType(int $a_type)
	{
		$this->login_prompt_type = $a_type;
	}

	/**
	 * @return int
	 */
	public function getLoginPromptType() : int
	{
		return $this->login_prompt_type;
	}

	/**
	 * @param int $a_scope
	 */
	public function setLogoutScope(int $a_scope)
	{
		$this->logout_scope = $a_scope;
	}

	/**
	 * @return int
	 */
	public function getLogoutScope() : int
	{
		return $this->logout_scope;
	}

	/**
	 * @param bool $a_stat
	 */
	public function useCustomSession(bool $a_stat)
	{
		$this->custom_session = $a_stat;
	}

	/**
	 * @return bool
	 */
	public function isCustomSession() : bool
	{
		return $this->custom_session;
	}

	/**
	 * @param int $a_duration
	 */
	public function setSessionDuration(int $a_duration)
	{
		$this->session_duration = $a_duration;
	}

	/**
	 * @return int
	 */
	public function getSessionDuration() : int
	{
		return $this->session_duration;
	}

	/**
	 * @return bool
	 */
	public function isSyncAllowed() : bool
	{
		return $this->allow_sync;
	}

	/**
	 * @param bool $a_stat
	 */
	public function allowSync(bool $a_stat)
	{
		$this->allow_sync = $a_stat;
	}

	/**
	 * @param int $role
	 */
	public function setRole(int $role)
	{
		$this->role = $role;
	}

	/**
	 * @return int
	 */
	public function getRole() : int
	{
		return $this->role;
	}

	/**
	 * @param string $field
	 */
	public function setUidField(string $field)
	{
		$this->uid = $field;
	}

	/**
	 * @return string
	 */
	public function getUidField() : string
	{
		return $this->uid;
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
	 * @param array $a_role_mappings
	 */
	public function setRoleMappings(array $a_role_mappings)
	{
		$this->role_mappings = $a_role_mappings;
	}

	/**
	 * Get role mappings
	 */
	public function getRoleMappings() : array
	{
		return (array) $this->role_mappings;
	}

	/**
	 * @param $a_role_id
	 * @return string
	 */
	public function getRoleMappingValueForId($a_role_id) : string {

		if(
			isset($this->role_mappings[$a_role_id]) &&
			isset($this->role_mappings[$a_role_id]['value'])
		) {
			return (string) $this->role_mappings[$a_role_id]['value'];
		}
		return '';
	}

	/**
	 * @param $a_role_id
	 * @return string
	 */
	public function getRoleMappingUpdateForId($a_role_id) : bool {

		if(
			isset($this->role_mappings[$a_role_id]) &&
			isset($this->role_mappings[$a_role_id]['update'])
		) {
			return (bool) $this->role_mappings[$a_role_id]['update'];
		}
		return '';
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
		$this->storage->set('prompt_type', $this->getLoginPromptType());
		$this->storage->set('logout_scope', $this->getLogoutScope());
		$this->storage->set('custom_session', (int) $this->isCustomSession());
		$this->storage->set('session_duration', (int) $this->getSessionDuration());
		$this->storage->set('allow_sync', (int) $this->isSyncAllowed());
		$this->storage->set('role', (int) $this->getRole());
		$this->storage->set('uid',(string) $this->getUidField());

		foreach($this->getProfileMappingFields() as $field => $lang_key)
		{
			$this->storage->set('pmap_'.$field, $this->getProfileMappingFieldValue($field));
			$this->storage->set('pumap_'.$field, $this->getProfileMappingFieldUpdate($field));
		}
		$this->storage->set('role_mappings', (string) serialize($this->getRoleMappings()));
	}

	/**
	 * Load from settings
	 */
	protected function load()
	{
		foreach($this->getProfileMappingFields() as $field => $lang_key)
		{
			$this->profile_map[$field] = (string) $this->storage->get('pmap_'.$field,'');
			$this->profile_update_map[$field] = (bool) $this->storage->get('pumap_'.$field,'');
		}

		$this->setActive((bool) $this->storage->get('active', 0));
		$this->setProvider($this->storage->get('provider', ''));
		$this->setClientId($this->storage->get('client_id',''));
		$this->setSecret($this->storage->get('secret',''));
		$this->setLoginElementImage($this->storage->get('le_img',''));
		$this->setLoginElementText($this->storage->get('le_text'));
		$this->setLoginElementType($this->storage->get('le_type'));
		$this->setLoginPromptType((int) $this->storage->get('prompt_type',self::LOGIN_ENFORCE));
		$this->setLogoutScope((int) $this->storage->get('logout_scope', self::LOGOUT_SCOPE_GLOBAL));
		$this->useCustomSession((bool) $this->storage->get('custom_session'), false);
		$this->setSessionDuration((int) $this->storage->get('session_duration', 60));
		$this->allowSync((bool) $this->storage->get('allow_sync'), false);
		$this->setRole((int) $this->storage->get('role'),0);
		$this->setUidField((string) $this->storage->get('uid'),'');
		$this->setRoleMappings((array) unserialize($this->storage->get('role_mappings', serialize([]))));
	}

	/**
	 * @param string $field
	 */
	public function getProfileMappingFieldValue(string $field) : string
	{
		return (string) $this->profile_map[$field];
	}

	/**
	 * @param string $field
	 * @param string $value
	 */
	public function setProfileMappingFieldValue(string $field, string $value)
	{
		$this->profile_map[$field] = $value;
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	public function getProfileMappingFieldUpdate(string $field) : bool
	{
		return (bool) $this->profile_update_map[$field];
	}

	/**
	 * @param string $field
	 * @param bool $value
	 */
	public function setProfileMappingFieldUpdate(string $field, bool $value)
	{
		$this->profile_update_map[$field] = $value;
	}


	/**
	 * @return array
	 */
	public function getProfileMappingFields() : array
	{
		return [
			'firstname' => 'firstname',
			'lastname' => 'lastname',
			'email'	=> 'email',
			'birthday' => 'birthday'
		];
	}

}
