<?php
/** 
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilCalendarUserSettingsGUI:
* @ingroup ServicesCalendar 
*/
include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
 
class ilCalendarUserSettings
{
	const CAL_SELECTION_MEMBERSHIP = 1;
	const CAL_SELECTION_ITEMS = 2;
	
	public static $instances = array();
	
	protected $user;
	protected $settings;
	
	private $calendar_selection_type = 1;
	private $timezone;
	private $weekstart;
	private $time_format;
	
	private $day_start;
	private $day_end;
	
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
	 * get instance for logged in user 
	 *
	 * @return
	 * @static
	 */
	 public static function _getInstance()
	 {
	 	global $ilUser;
	 	
	 	return self::_getInstanceByUserId($ilUser->getId());
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
	 * Set start of day
	 * @return 
	 * @param int $a_start
	 */
	public function setDayStart($a_start)
	{
		$this->day_start = 	$a_start;
	}
	
	/**
	 * get start of day
	 * @return 
	 */
	public function getDayStart()
	{
		return $this->day_start;
	}
	
	/**
	 * Set day end
	 * @return 
	 * @param int $a_end
	 */
	public function setDayEnd($a_end)
	{
		$this->day_end = $a_end;
	}
	
	/**
	 * Get end of day
	 * @return 
	 */
	public function getDayEnd()
	{
		return $this->day_end;
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
	 * get calendar selection type
	 * ("MyMembership" or "Selected Items") 
	 * 
	 * @return
	 */
	public function getCalendarSelectionType()
	{
		return $this->calendar_selection_type;		 
	}
	
	/**
	 * set calendar selection type 
	 * @param int $type self::CAL_SELECTION_MEMBERSHIP | self::CAL_SELECTION_ITEM
	 * @return void
	 */
	public function setCalendarSelectionType($a_type)
	{
		$this->calendar_selection_type = $a_type;
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
		$this->user->writePref('calendar_selection_type',$this->getCalendarSelectionType());
		$this->user->writePref('day_start',$this->getDayStart());
		$this->user->writePref('day_end',$this->getDayEnd());
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
		$this->calendar_selection_type = $this->user->getPref('calendar_selection_type') ?
			$this->user->getPref('calendar_selection_type') :
			self::CAL_SELECTION_MEMBERSHIP;

		$this->weekstart = $weekstart;
		
		$this->setDayStart($this->user->getPref('day_start') !== false ?
			$this->user->getPref('day_start') :
			ilCalendarSettings::DEFAULT_DAY_START
		);
		$this->setDayEnd($this->user->getPref('day_end') !== false ?
			$this->user->getPref('day_end') :
			ilCalendarSettings::DEFAULT_DAY_END
		);
	}
	
}
?>
