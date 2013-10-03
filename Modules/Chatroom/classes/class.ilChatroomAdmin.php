<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomAdmin
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomAdmin
{
	/**
	 * @var string
	 */
	private static $settingsTable = 'chatroom_admconfig';

	/**
	 * @var stdClass
	 */
	private $settings;

	/**
	 * @var int
	 */
	private $config_id;

	/**
	 * Constructor
	 * Sets $this->config_id and $this->settings using given $config_id
	 * and $settings
	 * @param integer  $config_id
	 * @param stdClass $settings
	 */
	public function __construct($config_id, stdClass $settings = null)
	{
		$this->config_id = $config_id;
		$this->settings  = $settings;
	}

	/**
	 * Instantiates and returns ilChatroomAdmin object using instance_id and settings
	 * from settingsTable.
	 * @return self
	 */
	public static function getDefaultConfiguration()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$ilDB->setLimit(1);
		$query = 'SELECT * FROM ' . self::$settingsTable;

		$rset = $ilDB->query($query);
		if($row = $ilDB->fetchObject($rset))
		{
			return new self((int)$row->instance_id, $row);
		}
	}

	/**
	 * Instantiates ilChatroomServerSettings object, sets data using
	 * $this->settings->server_settings and returns object.
	 * @return ilChatroomServerSettings
	 */
	public function getServerSettings()
	{
		require_once 'Modules/Chatroom/classes/class.ilChatroomServerSettings.php';
		return ilChatroomServerSettings::loadDefault();
	}

	/**
	 * Saves given $settings into settingsTable.
	 * @param stdClass $settings
	 */
	public function saveGeneralSettings(stdClass $settings)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$res = $ilDB->queryF("
				SELECT 	* 
				FROM 	chatroom_admconfig
				WHERE	instance_id = %s",
			array('integer'),
			array($this->config_id)
		);

		$row = $ilDB->fetchAssoc($res);

		$ilDB->manipulateF("
			DELETE 
			FROM 	chatroom_admconfig
			WHERE	instance_id = %s",
			array('integer'),
			array($this->config_id)
		);

		$row['default_config'] !== null ? $def_conf = $row['default_config'] : $def_conf = "{}";
		$row['client_settings'] !== null ? $clnt_set = $row['client_settings'] : $clnt_set = "{}";

		$ilDB->manipulateF("
			INSERT INTO		chatroom_admconfig
							(instance_id, server_settings, default_config, client_settings)
			VALUES			(%s, %s, %s, %s)",
			array('integer', 'text', 'integer', 'text'),
			array($this->config_id, json_encode($settings), $def_conf, $clnt_set)
		);
	}

	/**
	 * Saves given client $settings into settingsTable.
	 * @param stdClass $settings
	 */
	public function saveClientSettings(stdClass $settings)
	{
		/**
		 * @var $ilDB
		 */
		global $ilDB;

		$res = $ilDB->queryF("
				SELECT 	* 
				FROM 	chatroom_admconfig
				WHERE	instance_id = %s",
			array('integer'),
			array($this->config_id)
		);

		$row = $ilDB->fetchAssoc($res);

		$ilDB->manipulateF("
			DELETE 
			FROM 	chatroom_admconfig
			WHERE	instance_id = %s",
			array('integer'),
			array($this->config_id)
		);

		$row['default_config'] !== null ? $def_conf = $row['default_config'] : $def_conf = "{}";
		$row['server_settings'] !== null ? $srv_set = $row['server_settings'] : $srv_set = "{}";

		$ilDB->manipulateF("
			INSERT INTO		chatroom_admconfig
							(instance_id, server_settings, default_config, client_settings)
			VALUES			(%s, %s, %s, %s)",
			array(
				'integer',
				'text',
				'integer',
				'text'
			),
			array(
				$this->config_id,
				$srv_set,
				$def_conf,
				json_encode($settings)
			)
		);
	}

	/**
	 * Returns an array containing server settings from settingsTable.
	 * @return null|stdClass
	 */
	public function loadGeneralSettings()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$query = 'SELECT * FROM ' . self::$settingsTable . ' WHERE instance_id = ' . $ilDB->quote($this->config_id, 'integer');

		if(($row = $ilDB->fetchAssoc($ilDB->query($query))) && $row['server_settings'])
		{
			return json_decode($row['server_settings']);
		}

		return null;
	}

	/**
	 * Returns an array containing client settings from settingsTable.
	 * @return null|stdClass
	 */
	public function loadClientSettings()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$query = 'SELECT * FROM ' . self::$settingsTable . ' WHERE instance_id = ' . $ilDB->quote($this->config_id, 'integer');
		if(($row = $ilDB->fetchAssoc($ilDB->query($query))) && $row['client_settings'])
		{
			return json_decode($row['client_settings']);
		}

		return null;
	}
}
