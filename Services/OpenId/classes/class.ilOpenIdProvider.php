<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @classDescription OpenId provider
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 */
class ilOpenIdProvider
{
	private $provider_id = 0;
	private $name = '';
	private $url = 'http://';
	private $enabled = true;
	private $image = 0;

	/**
	 * Constructor
	 */
	public function __construct($a_provider_id = 0)
	{
		if($a_provider_id)
		{
			$this->setId($a_provider_id);
			$this->read();
		}
	}
	
	/**
	 * Set id
	 * @param int $a_id
	 * @return 
	 */
	public function setId($a_id)
	{
		$this->provider_id = $a_id;
	}
	
	/**
	 * Get id
	 * @return 
	 */
	public function getId()
	{
		return $this->provider_id;
	}
	
	/**
	 * Set en/disabled
	 * @param bool $a_status
	 * @return 
	 */
	public function enable($a_status)
	{
		$this->enabled = (bool) $a_status;
	}
	
	/**
	 * Check if provider is en/disabled
	 * @return 
	 */
	public function isEnabled()
	{
		return (bool) $this->enabled;
	}
	
	/**
	 * Set name
	 * @param string $a_name
	 * @return 
	 */
	public function setName($a_name)
	{
		$this->name = $a_name;
	}
	
	/**
	 * Get name
	 * @return 
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Set URL
	 * @param string $a_url
	 * @return 
	 */
	public function setURL($a_url)
	{
		$this->url = $a_url;
	}

	/**
	 * Get URL
	 * @return 
	 */
	public function getURL()
	{
		return $this->url;
	}
	
	/**
	 * Delete provider
	 * @return 
	 */
	public function delete()
	{
		global $ilDB;
		
		$query = "DELETE FROM openid_provider ".
			"WHERE provider_id = ".$ilDB->quote($this->getId(),'integer');
		$ilDB->query($query);
		return true;
	}
	
	/**
	 * Add openid provider
	 * @return 
	 */
	public function add()
	{
		global $ilDB;
		
		$this->setId($ilDB->nextId('openid_provider'));
		$query = "INSERT INTO openid_provider ".
			"(provider_id, enabled, name, url) ".
			"VALUES ( ".
			$ilDB->quote($this->getId(),'integer').', '.
			$ilDB->quote($this->isEnabled(),'integer').', '.
			$ilDB->quote($this->getName(),'text').', '.
			$ilDB->quote($this->getURL(),'text').
			')';
		$ilDB->query($query);
		return true;
	}
	
	/**
	 * Update provider
	 * @return 
	 */
	public function update()
	{
		global $ilDB;
		
		$query = 'UPDATE openid_provider SET '.
			"enabled = ".$ilDB->quote($this->isEnabled(),'integer').', '.
			"name = ".$ilDB->quote($this->getName(),'text').', '.
			"url = ".$ilDB->quote($this->getURL(),'text')." ".
			"WHERE provider_id = ".$ilDB->quote($this->getId(),'integer');
		$ilDB->query($query);
		return true;
	}
	
	
	/**
	 * Read provider data
	 * @return 
	 */
	protected function read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM openid_provider ".
			"WHERE provider_id = ".$ilDB->quote($this->getId(),'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->setName($row['name']);
			$this->enable($row['enabled']);
			$this->setURL($row['url']);
			return true;
		}
		return false;
	}
}
?>