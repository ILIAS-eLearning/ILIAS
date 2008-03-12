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
	protected $user;
	protected $settings;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($user)
	{
		$this->user = $user;
		$this->settings = ilCalendarSettings::_getInstance();
		$this->read();
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
		return $this->weekstart;
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
	}
	
	
	/**
	 * read
	 *
	 * @access protected
	 */
	protected function read()
	{
		$this->timezone = $this->user->getUserTimeZone();
		if(($weekstart = $this->user->getPref('weekstart')) === false)
		{
			$weekstart = $this->settings->getDefaultWeekStart();
		}
		$this->weekstart = $weekstart;
	}
	
}
?>
