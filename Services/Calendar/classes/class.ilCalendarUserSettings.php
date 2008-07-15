<?php
/*
 * Created on 06.03.2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
 
class ilCalendarUserSettings
{
	public static $instances = array();
	
	protected $user;
	protected $settings;
	
	private $timezone;
	private $weekstart;
	private $time_format;
	
	/**
	 * Constructor
	 *
	 * @access private
	 * @param
	 * @return
	 */
	private function __construct($a_user_id)
	{
		global $ilUser;
		
		if($ilUser->getId() == $a_user_id)
		{
			$this->user = $ilUser;
		}
		else
		{
			$this->user = ilObjectFactory::getInstanceByObjId($a_user_id,false);
		}
		$this->settings = ilCalendarSettings::_getInstance();
		$this->read();
	}
	
	/**
	 * get singleton instance
	 *
	 * @access public
	 * @param int user id
	 * @return ilCalendarUserSettings
	 * @static
	 */
	public static function _getInstanceByUserId($a_user_id)
	{
		if(isset(self::$instances[$a_user_id]))
		{
			return self::$instances[$a_user_id];
		}
		return self::$instances[$a_user_id] = new ilCalendarUserSettings($a_user_id);
	}
	
	/**
	 * get Time zone
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getTimeZone()
	{
		return $this->timezone;
	}
	
	/**
	 * set timezone
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function setTimeZone($a_tz)
	{
		$this->timezone = $a_tz;
	}
	
	/**
	 * set week start
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function setWeekStart($a_weekstart)
	{
		$this->weekstart = $a_weekstart;
	}
	
	/**
	 * get weekstart
	 *
	 * @access public
	 * @return
	 */
	public function getWeekStart()
	{
		return (int) $this->weekstart;
	}
	
	/**
	 * set time format
	 *
	 * @access public
	 * @param int time
	 * @return
	 */
	public function setTimeFormat($a_format)
	{
		$this->time_format = $a_format;
	}
	
	/**
	 * get time format
	 *
	 * @access public
	 * @return int time format
	 */
	public function getTimeFormat()
	{
		return $this->time_format;
	}

	/**
	 * save
	 *
	 * @access public
	 */
	public function save()
	{
		$this->user->writePref('user_tz',$this->getTimeZone());
		$this->user->writePref('weekstart',$this->getWeekStart()); 
		$this->user->writePref('time_format',$this->getTimeFormat());
	}
	
	
	/**
	 * read
	 *
	 * @access protected
	 */
	protected function read()
	{
		$this->timezone = $this->user->getTimeZone();
		$this->time_format = $this->user->getTimeFormat();
		if(($weekstart = $this->user->getPref('weekstart')) === false)
		{
			$weekstart = $this->settings->getDefaultWeekStart();
		}
		$this->weekstart = $weekstart;
	}
	
}
?>
