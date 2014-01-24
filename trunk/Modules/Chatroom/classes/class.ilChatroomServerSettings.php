<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomServerSettings
 *
 * @author Andreas Kordosz <akordosz@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomServerSettings
{

	private $port		= '8585';
	private $protocol	= 'http://';
	private $domain		= '192.168.1.94';
	private $instance	= '123456';

	/**
	 * Returns $this->port.
	 *
	 * @return string
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * Sets $this->port using given $port
	 *
	 * @param string $port
	 */
	public function setPort($port)
	{
		$this->port = $port;
	}

	/**
	 * Returns $this->protocol.
	 *
	 * @return string
	 */
	public function getProtocol()
	{
		return $this->protocol;
	}

	/**
	 * Sets $this->protocol using given $protocol
	 *
	 * @param string $protocol
	 */
	public function setProtocol($protocol)
	{
		$this->protocol = $protocol . '://';
	}

	/**
	 * Returns $this->domain.
	 *
	 * @return string
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * Sets $this->domain using given $domain.
	 *
	 * @param string $domain
	 */
	public function setDomain($domain)
	{
		$this->domain = $domain;
	}

	/**
	 * Creates URL by calling $this->getBaseURL and using given $action and
	 * $scope and returns it.
	 *
	 * @param string $action
	 * @param string $scope
	 * @return string
	 */
	public function getURL($action, $scope)
	{
		return $this->getBaseURL() .
		'/backend/' . $action . '/' . $this->getInstance() . '/' . $scope;
	}

	/**
	 * Returns base URL
	 *
	 * Creates base URL by calling $this->getProtocol(), $this->getDomain() and
	 * $this->getPort() and returnes it.
	 *
	 * @return string
	 */
	public function getBaseURL()
	{
		return $this->getProtocol() . $this->getDomain() . ':' . $this->getPort();
	}

	/**
	 * Returns $this->instance.
	 *
	 * @return string
	 */
	public function getInstance()
	{
		return $this->instance;
	}

	/**
	 * Sets $this->instance using given $instance
	 *
	 * @param string $instance
	 */
	public function setInstance($instance)
	{
		$this->instance = $instance;
	}
	
	public function setSmiliesEnabled($a_bool) {
	    $this->smilies_enabled = $a_bool;
	}
	
	public function getSmiliesEnabled() {
	    return (bool) $this->smilies_enabled;
	}

	public static function loadDefault() {
		global $ilDB;

		$query = 'SELECT * FROM chatroom_admconfig';
		$rset = $ilDB->query($query);
		$row = $ilDB->fetchAssoc($rset);

		$client_settings = json_decode($row['client_settings']);
		$server_settings = json_decode($row['server_settings']);

		$settings = new ilChatroomServerSettings();
		$settings->setPort($server_settings->port);
		$settings->setProtocol($server_settings->protocol);
		$settings->setInstance($client_settings->name);
		$settings->setDomain($server_settings->address);
		$settings->setSmiliesEnabled($client_settings->enable_smilies);
		return $settings;
	}

}

?>