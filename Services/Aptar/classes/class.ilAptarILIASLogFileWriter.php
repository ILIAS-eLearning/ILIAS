<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Aptar/classes/class.ilAptarBaseLogWriter.php';

/**
 * Class ilAptarILIASLogFileWriter
 */
class ilAptarILIASLogFileWriter extends ilAptarBaseLogWriter
{
	/**
	 * @var null|ilLog
	 */
	protected $aggregated_logger;

	/**
	 * @var null|ilAptarInterfaceLogOverviewPlugin
	 */
	protected $log_overview_plugin = null;

	/**
	 * @var string
	 */
	protected $filename = '';

	/**
	 * @var array
	 */
	protected $logged_priorities = array();

	/**
	 * @var int
	 */
	protected $succeeded_users = 0;

	/**
	 * @var int
	 */
	protected $start_ts      = 0;

	/**
	 * @return int
	 */
	public static function getUnixTimestampAsMilliSeconds()
	{
		list($usec, $sec) = explode(' ', microtime());
		return (int) ((int) $sec * 1000 + ((float) $usec * 1000));
	}

	/**
	 *
	 */
	public function __construct()
	{
		$now = new DateTime('now');
		$file = $now->format('YmdHis_') . self::getUnixTimestampAsMilliSeconds() . '_' . 'soap_user_import.log';
		$tmpdir = ilUtil::ilTempnam();
		ilUtil::makeDir($tmpdir);

		$this->setFilename($tmpdir . DIRECTORY_SEPARATOR . $file);

		foreach($GLOBALS['ilPluginAdmin']->getActivePluginsForSlot(IL_COMP_SERVICE, 'UIComponent', 'uihk') as $plugin_name)
		{
			$plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, 'UIComponent',  'uihk', $plugin_name);
			if(class_exists('ilAptarInterfaceLogOverviewPlugin') && $plugin instanceof ilAptarInterfaceLogOverviewPlugin)
			{
				$this->log_overview_plugin = $plugin;
				break;
			}
		}

		$this->aggregated_logger = new ilLog($tmpdir, $file);
		$this->aggregated_logger->setLogFormat('');
	}

	/**
	 * @param array $message
	 * @return void
	 */
	protected function doWrite(array $message)
	{
		if($this->start_ts == 0)
		{
			$this->start_ts = time(); 
		}

		if(isset($message['extra']) && isset($message['extra']['import_success']))
		{
			++$this->succeeded_users;
		}

		if(isset($message['priority']))
		{
			if(!isset($this->logged_priorities[$message['priority']]))
			{
				$this->logged_priorities[$message['priority']] = 1;
			}
			else
			{
				++$this->logged_priorities[$message['priority']];
			}
		}

		$line = $this->format($message);
		$this->aggregated_logger->write($line);
	}

	/**
	 * @return int
	 */
	protected function getHighestLoggedSeverity()
	{
		foreach(array(
					ilAptarLog::EMERG,
					ilAptarLog::ALERT,
					ilAptarLog::CRIT,
					ilAptarLog::ERR,
					ilAptarLog::WARN,
					ilAptarLog::NOTICE,
					ilAptarLog::INFO,
					ilAptarLog::DEBUG
				) as $severity)
		{
			if(isset($this->logged_priorities[$severity]) && $this->logged_priorities[$severity] > 0)
			{
				return $severity;
			}
		}

		return PHP_INT_MAX;
	}

	/**
	 * @return void
	 */
	public function shutdown()
	{
		if($this->log_overview_plugin !== null)
		{
			$this->log_overview_plugin->getReportingData(
				$this->getFilename(),
				(int)$this->logged_priorities[ilAptarLogger::ERR] + (int)$this->logged_priorities[ilAptarLogger::CRIT] +
				(int)$this->logged_priorities[ilAptarLogger::ALERT] + (int)$this->logged_priorities[ilAptarLogger::EMERG],
				(int)$this->logged_priorities[ilAptarLogger::WARN],
				$this->succeeded_users,
				$this->start_ts > 0 ? time() - $this->start_ts : 0,
				$this->getHighestLoggedSeverity()
			);
		}

		unset($this->log_overview_plugin);
		unset($this->aggregated_logger);
	}

	/**
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * @param string $filename
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;
	}
}