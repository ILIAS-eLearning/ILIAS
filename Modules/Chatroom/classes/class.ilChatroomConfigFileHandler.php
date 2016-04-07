<?php


/**
 * Class ilChatroomConfigFileHandler
 * @package Modules\Chatroom\classes
 * @author  Thomas Joußen <tjoussen@databay.de>
 * @since   19.02.16
 * @version $Id$
 */
class ilChatroomConfigFileHandler
{

	const CHATROOM_DATA_DIR = '/chatroom/';
	const CHATROOM_CLIENT_CONFIG_FILENAME = 'client.cfg';
	const CHATROOM_SERVER_CONFIG_FILENAME = 'server.cfg';

	/**
	 * Creates a client config file and saves it to the chatroom data directory
	 *
	 * @param array $settings
	 *
	 * @throws Exception
	 */
	public function createClientConfigFile(array $settings)
	{
		$content = $this->getClientFileContent($settings);
		$this->writeDataToFile($content, self::CHATROOM_CLIENT_CONFIG_FILENAME);
	}

	/**
	 * Creates a server config file and saves it to the chatroom data directory
	 *
	 * @param array $settings
	 *
	 * @throws Exception
	 */
	public function createServerConfigFile(array $settings)
	{
		$content = $this->getServerFileContent($settings);
		$this->writeDataToFile($content, self::CHATROOM_SERVER_CONFIG_FILENAME);
	}

	/**
	 * Get the client config file content as json encoded string
	 *
	 * @param array $settings
	 *
	 * @return string
	 */
	protected function getClientFileContent(array $settings)
	{
		/** @var ilDB $ilDB */
		global $ilDB;

		// Dirty configuration swap: Ilias differentiates between InnoDB and MyISAM.
		// MyISAM is configured as mysql, InnoDB as innodb.
		// The client config file only needs information about driver not engine
		$type = $ilDB->getDBType();
		if($type == 'innodb')
		{
			$type = 'mysql';
		}

		$settings['database'] = array(
			'type' => $type,
			'host' => $ilDB->getDBHost(),
			'port' => $ilDB->getDBPort(),
			'name' => $ilDB->getDBName(),
			'user' => $ilDB->getDBUser(),
			'pass' => $ilDB->getDBPassword()
		);

		return json_encode($settings, JSON_PRETTY_PRINT);
	}

	/**
	 * Get the server config file contetn as json encoded string
	 *
	 * @param array $settings
	 *
	 * @return string
	 */
	protected function getServerFileContent(array $settings)
	{
		unset($settings['ilias_proxy']);
		unset($settings['client_proxy']);
		unset($settings['ilias_url']);
		unset($settings['client_url']);

		return json_encode($settings, JSON_PRETTY_PRINT);
	}

	/**
	 * Writes $content to file named by $filename
	 *
	 * @param string $content
	 * @param string $filename
	 *
	 * @throws Exception
	 */
	protected function writeDataToFile($content, $filename)
	{
		$path = $this->createDataDirIfNotExists();
		$handle = fopen($path . $filename, 'w');

		if(!fwrite($handle, $content))
		{
			throw new Exception('Cannot write to file');
		}

		fclose($handle);
	}

	/**
	 * Creates a data directory for configuration files, if the directory does not already exists.
	 *
	 * @return string
	 * @throws Exception Throws Exception if data dir creation failed
	 */
	protected function createDataDirIfNotExists()
	{
		$path = ilUtil::getDataDir() . self::CHATROOM_DATA_DIR;

		if(!file_exists($path))
		{
			if(!ilUtil::makeDir($path))
			{
				throw new Exception('Directory cannot be created');
			}
		}

		return $path;
	}
}
