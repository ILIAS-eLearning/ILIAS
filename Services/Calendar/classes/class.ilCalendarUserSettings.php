<?php
/*
 * Created on 06.03.2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
class ilCalendarUserSettings
{
	protected $user;
	
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
	 * save
	 *
	 * @access public
	 */
	public function save()
	{
		$this->user->writePref('user_tz',$this->getTimeZone());
	}
	
	
	/**
	 * read
	 *
	 * @access protected
	 */
	protected function read()
	{
		$this->timezone = $this->user->getUserTimeZone();
	}
	
}
?>
