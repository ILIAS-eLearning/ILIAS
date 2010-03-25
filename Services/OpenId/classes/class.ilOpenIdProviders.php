<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/OpenId/classes/class.ilOpenIdProvider.php';

/**
 * @classDescription OpenId provider
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 */
class ilOpenIdProviders
{
	private static $instance = null;
	
	private $providers = array();

	/**
	 * Singleton constructor
	 * @return 
	 */
	protected function __construct()
	{
		$this->read();
	}
	
	/**
	 * Get singleton instance
	 * @return object ilOpenIdProviders 
	 */
	public static function getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilOpenIdProviders();
	}
	
	/**
	 * Get enabled provider
	 * @return 
	 */
	public function getProvider()
	{
		return (array) $this->providers;
	}
	
	/**
	 * Get provider by id
	 * @param object $a_provider_id
	 * @return object ilOpenIdProvider
	 * @throws UnexpectedValueException
	 */
	public function getProviderById($a_provider_id)
	{
		foreach($this->getProvider() as $provider)
		{
			if($provider->getId() == $a_provider_id)
			{
				return $provider;
			}
		}
		throw new UnexpectedValueException();
	}
	
	/**
	 * get html select options
	 * @return array $options
	 */
	public function getProviderSelection()
	{
		global $lng;
		
		$options[0] = $lng->txt('select_one');
		foreach($this->getProvider() as $provider)
		{
			$options[$provider->getId()] = $provider->getName();
		}
		return $options;
	}
	
	/**
	 * Read providers
	 * @return 
	 */
	private function read()
	{
		global $ilDB;
		
		$query = "SELECT provider_id FROM openid_provider ORDER BY name ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->providers[] = new ilOpenIdProvider($row['provider_id']);
		}
		return true;
	}
}
?>