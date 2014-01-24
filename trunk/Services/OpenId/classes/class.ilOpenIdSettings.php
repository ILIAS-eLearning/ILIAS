<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Administration/classes/class.ilSetting.php';


/**
 * @classDescription Stores OpenId related settings
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 */
class ilOpenIdSettings
{
	private static $instance = null;

	private $storage = null;
	
	private $active = false;
	private $account_migration = false;
	private $default_role = 0;
	private $creation = false;
	private $forced_selection = false;
	
	private $consumer = null;

	/**
	 * Singleton constructor
	 */
	private function __construct()
	{
		$this->storage = new ilSetting('auth_openid');
		$this->read();
	}
	
	/**
	 * Get singleton instance
	 * @return object ilOpenIdSettings
	 */
	public static function getInstance()
	{
		if(self::$instance != null)
		{
			return self::$instance;
		}
		return self::$instance = new ilOpenIdSettings();
	}
	
	
	/**
	 * Is open id auth active
	 * @return 
	 */
	public function isActive()
	{
		return (bool) $this->active;
	}
	
	/**
	 * Set open id active
	 * @param bool $a_status
	 * @return 
	 */
	public function setActive($a_status)
	{
		$this->active = $a_status;
	}
	
	/**
	 * is provider selection forced
	 * @return 
	 */
	public function forcedProviderSelection()
	{
		return $this->forced_selection;
	}
	
	/**
	 * Set force selection status
	 * @param bool $a_status
	 * @return 
	 */
	public function forceProviderSelection($a_status)
	{
		$this->forced_selection = $a_status;
	}
	
	/**
	 * Is account creation enabled
	 * @return 
	 */
	public function isCreationEnabled()
	{
		return (bool) $this->creation;
	}
	
	/**
	 * Enable account creation
	 * @param bool $a_status
	 * @return 
	 */
	public function enableCreation($a_status)
	{
		$this->creation = $a_status;
	}
	
	/**
	 * Is account migration enabled
	 * @return 
	 */
	public function isAccountMigrationEnabled()
	{
		return (bool) $this->account_migration;
	}
	
	/**
	 * Enable account migration
	 * @param bool $a_status
	 * @return 
	 */
	public function enableAccountMigration($a_status)
	{
		$this->account_migration = $a_status;
	}
	
	/**
	 * Get default role
	 * @return 
	 */
	public function getDefaultRole()
	{
		return $this->default_role;
	}
	
	/**
	 * Set default role
	 * @param int $a_role
	 * @return 
	 */
	public function setDefaultRole($a_role)
	{
		$this->default_role = $a_role;
	}
	
	/**
	 * Read settings from db
	 * @return 
	 */
	protected function read()
	{
		$this->setActive($this->storage->get('active',false));
		$this->enableCreation($this->storage->get('creation',false));
		$this->setDefaultRole($this->storage->get('default_role',0));
		$this->enableAccountMigration($this->storage->get('account_migration',false));
		$this->forceProviderSelection($this->storage->get('forced_selection',false));
	}
	
	/**
	 * Update settings
	 * @return 
	 */
	public function update()
	{
		$this->storage->set('active', (int) $this->isActive());
		$this->storage->set('creation',(int) $this->isCreationEnabled());
		$this->storage->set('default_role',(int) $this->getDefaultRole());
		$this->storage->set('account_migration',(int) $this->isAccountMigrationEnabled());
		$this->storage->set('forced_selection',(int) $this->forcedProviderSelection());
	}
	
	/**
	 * Get open id consumer
	 * @return 
	 */
	public function getConsumer()
	{
		return $this->consumer;
	}
	
	/**
	 * Get oid return location
	 * @return 
	 */
	public function getReturnLocation()
	{
		global $ilCtrl;

		$ilCtrl->setTargetScript('ilias.php');
		$ilCtrl->setParameterByClass('ilstartupgui','oid_check_status',1);
		$redir = ILIAS_HTTP_PATH.'/';
		$redir .= $ilCtrl->getLinkTargetByClass('ilstartupgui','showLogin','',false,false);
		return $redir;
	}
	
	/**
	 * Init Temp directory
	 * @return 
	 */
	protected function initTempDir()
	{
		if(!file_exists(ilUtil::getDataDir().DIRECTORY_SEPARATOR.'tmp'))
		{
			ilUtil::makeDir(ilUtil::getDataDir().DIRECTORY_SEPARATOR.'tmp');
		}
		return true;
	}
	
	public function initConsumer()
	{
		include_once "Auth/OpenID/Consumer.php";
  		include_once "Auth/OpenID/FileStore.php";
		include_once 'Auth/OpenID/DumbStore.php';
		
		if(is_object($this->consumer))
		{
			return true;
		}
		
		$this->initTempDir();
		$store = new Auth_OpenID_FileStore(ilUtil::getDataDir().DIRECTORY_SEPARATOR.'tmp');
		return $this->consumer = new Auth_OpenID_Consumer($store);
	}
	
}
?>