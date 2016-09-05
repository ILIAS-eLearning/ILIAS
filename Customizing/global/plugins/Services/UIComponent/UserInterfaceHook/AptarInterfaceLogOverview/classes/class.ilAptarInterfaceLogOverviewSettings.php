<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAptarInterfaceLogOverviewSettings
 */
class ilAptarInterfaceLogOverviewSettings
{

	/**
	 * @const int defined from the BSD Syslog message severities
	 * @link http://tools.ietf.org/html/rfc3164
	 */
	const EMERG  = 0;
	const ALERT  = 1;
	const CRIT   = 2;
	const ERR    = 3;
	const WARN   = 4;
	const NOTICE = 5;
	const INFO   = 6;
	const DEBUG  = 7;

	/**
	 * @var int
	 */
	const DEFAULT_CLEANUP_BOUNDARY_VALUE = 6;

	/**
	 * @var int
	 */
	const DEFAULT_CLEANUP_BOUNDARY_UNIT = 'months';

	/**
	 * @var self
	 */
	private static $instance;

	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * @var bool
	 */
	private $is_cleanup_enabled = false;

	/**
	 * @var int
	 */
	private $cleanup_boundary_value = self::DEFAULT_CLEANUP_BOUNDARY_VALUE;

	/**
	 * @var string
	 */
	private $cleanup_boundary_unit = self::DEFAULT_CLEANUP_BOUNDARY_UNIT;

	/**
	 * @var array
	 */
	protected static $boundary_units = array(
		'days',
		'weeks',
		'months',
		'years'
	);

	/**
	 * @var bool
	 */
	private $is_mail_enabled;

	/**
	 * @var string
	 */
	private $recipients;

	/**
	 * @var string
	 */
	private $subject;

	/**
	 * @var int
	 */
	private $error_level;

	/**
	 * @var bool
	 */
	private $add_attachment;
	
	/** @var $ilDB   ilDB */
	protected $db;

	/**
	 *
	 */
	private function __construct()
	{
		/**
		 * @var $ilDB   ilDB
		 */
		global $ilDB;
		
		$this->settings = new ilSetting('customizing_aptar_ilo');
		$this->read();
		$this->db = $ilDB;
	}

	/**
	 * Get singleton instance
	 *
	 * @return self
	 */
	public static function getInstance()
	{
		if(null !== self::$instance)
		{
			return self::$instance;
		}

		return (self::$instance = new self());
	}

	/**
	 *
	 */
	protected function read()
	{
		$this->setIsCleanupEnabled($this->settings->get('cleanup_status', 0));
		$this->setCleanupBoundaryValue($this->settings->get('cleanup_boundary_value', self::DEFAULT_CLEANUP_BOUNDARY_VALUE));
		$this->setCleanupBoundaryUnit($this->settings->get('cleanup_boundary_unit', self::DEFAULT_CLEANUP_BOUNDARY_UNIT));

		$this->setIsMailEnabled($this->settings->get('is_mail_enabled', 0));
		$this->setRecipients(array());
		if(strlen($this->settings->get('recipients', '')))
		{
			$this->setRecipients(unserialize($this->settings->get('recipients', '')));
		}
		$this->setSubject($this->settings->get('subject', '[ILIAS Sync Log] %s'));
		$this->setErrorLevel($this->settings->get('error_level', ''));
		$this->setAddAttachment($this->settings->get('add_attachment', 0));
	}

	/**
	 *
	 */
	public function save()
	{
		$this->settings->set('cleanup_status', (int)$this->isCleanupEnabled());
		$this->settings->set('cleanup_boundary_value', $this->getCleanupBoundaryValue());
		$this->settings->set('cleanup_boundary_unit', $this->getCleanupBoundaryUnit());
		$this->settings->set('is_mail_enabled', (int)$this->isMailEnabled());
		$this->settings->set('recipients', serialize((array)$this->getRecipients()));
		$this->settings->set('subject', $this->getSubject());
		$this->settings->set('error_level', $this->getErrorLevel());
		$this->settings->set('add_attachment',(int) $this->isAddAttachment());
	}

	/**
	 * @return bool
	 */
	public function isConfigurationComplete()
	{
		$settings = array();
		foreach($settings as $setting)
		{
			if(!strlen($setting))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @param $log_id
	 * @return string
	 */
	public function getFileNameForLogId($log_id)
	{
		$res	= $this->db->query('SELECT * FROM aptar_ilo_log_table WHERE log_id=' . $log_id);
		$row = $this->db->fetchAssoc($res);
		return $row['file_path'];
	}

	/**
	 * @param $file_name
	 * @return string
	 */
	public function getLogIdFromFilename($file_name)
	{
		$res	= $this->db->queryF('SELECT * FROM aptar_ilo_log_table WHERE file_path = %s', array('text'), array($file_name));
		$row = $this->db->fetchAssoc($res);
		return $row['log_id'];
	}

	/**
	 * @param array $log_ids
	 * @return array
	 */
	public function removeDataFromDb($log_ids)
	{
		$in	= $this->db->in('log_id',$log_ids, false, 'integer');
		$this->db->query('DELETE FROM aptar_ilo_log_table WHERE ' . $in);
	}

	/**
	 * @return boolean
	 */
	public function isCleanupEnabled()
	{
		return (bool)$this->is_cleanup_enabled;
	}

	/**
	 * @param boolean $is_cleanup_enabled
	 */
	public function setIsCleanupEnabled($is_cleanup_enabled)
	{
		$this->is_cleanup_enabled = (bool)$is_cleanup_enabled;
	}
	/**
	 * @return array
	 */
	public static function getBoundaryUnits()
	{
		return self::$boundary_units;
	}

	/**
	 * @return string
	 */
	public function getCleanupBoundaryUnit()
	{
		return $this->cleanup_boundary_unit;
	}

	/**
	 * @param string $cleanup_boundary_unit
	 * @throws InvalidArgumentException
	 */
	public function setCleanupBoundaryUnit($cleanup_boundary_unit)
	{
		if(!in_array($cleanup_boundary_unit, self::$boundary_units))
		{
			throw new InvalidArgumentException(sprintf("Invalid unit passed: %s", $cleanup_boundary_unit));
		}

		$this->cleanup_boundary_unit = $cleanup_boundary_unit;
	}

	/**
	 * @return int
	 */
	public function getCleanupBoundaryValue()
	{
		return (int)$this->cleanup_boundary_value;
	}

	/**
	 * @param int $cleanup_boundary_value
	 */
	public function setCleanupBoundaryValue($cleanup_boundary_value)
	{
		$this->cleanup_boundary_value = (int)$cleanup_boundary_value;
	}

	/**
	 * @return boolean
	 */
	public function isMailEnabled()
	{
		return $this->is_mail_enabled;
	}

	/**
	 * @param boolean $is_mail_enabled
	 */
	public function setIsMailEnabled($is_mail_enabled)
	{
		$this->is_mail_enabled = $is_mail_enabled;
	}

	/**
	 * @return string
	 */
	public function getRecipients()
	{
		return $this->recipients;
	}

	/**
	 * @param string $recipients
	 */
	public function setRecipients($recipients)
	{
		$this->recipients = $recipients;
	}

	/**
	 * @return string
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * @param string $subject
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
	}

	/**
	 * @return int
	 */
	public function getErrorLevel()
	{
		return $this->error_level;
	}

	/**
	 * @param int $error_level
	 */
	public function setErrorLevel($error_level)
	{
		$this->error_level = $error_level;
	}

	/**
	 * @return boolean
	 */
	public function isAddAttachment()
	{
		return $this->add_attachment;
	}

	/**
	 * @param boolean $add_attachment
	 */
	public function setAddAttachment($add_attachment)
	{
		$this->add_attachment = $add_attachment;
	}
}