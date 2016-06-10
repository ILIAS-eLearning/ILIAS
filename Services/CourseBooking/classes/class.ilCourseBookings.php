<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/CourseBooking/classes/class.ilCourseBookingHelper.php";
require_once "Services/CourseBooking/classes/class.ilCourseBooking.php";

/**
 * Course bookings
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCourseBooking
 */
class ilCourseBookings 
{
	protected $course; // [ilObjCourse]
	
	static protected $instances = array();
	static protected $waiting_instances = array();
	static protected $user_status = array();
	
	/**
	 * Constructor
	 * 
	 * @param ilObjCourse $a_course
	 * @return self
	 */	
	protected function __construct(ilObjCourse $a_course)
	{
		$this->setCourse($a_course);

		//gev-patch start
		global $ilLog;
		$this->gLog = $ilLog;
		//gev-patch end
	}
	
	/**
	 * Factory
	 * 
	 * @param ilObjCourse $a_course
	 * @return self
	 */
	public static function getInstance(ilObjCourse $a_course)
	{
		$crs_ref_id = $a_course->getRefId();
		
		if(!array_key_exists($crs_ref_id, self::$instances))
		{
			self::$instances[$crs_ref_id] = new self($a_course);
		}
		
		return self::$instances[$crs_ref_id];
	}
	
	/**
	 * Factory
	 * 
	 * @param int $a_crs_ref_id
	 * @return self
	 */
	public static function getInstanceByRefId($a_course_ref_id)
	{		
		global $tree;
		
		if(array_key_exists($a_course_ref_id, self::$instances))
		{
			return self::$instances[$a_course_ref_id];
		}		
		
		if(ilObject::_lookupType($a_course_ref_id, true) != "crs" ||
			$tree->isDeleted($a_course_ref_id))
		{
			throw new ilException("ilCourseBookings - needs course ref id");
		}
		
		require_once "Modules/Course/classes/class.ilObjCourse.php";
		$course = new ilObjCourse($a_course_ref_id);
		
		return self::getInstance($course);		
	}
	
	/**
	 * Init waiting list instance for course
	 * 
	 * @return ilCourseWaitingList
	 */
	protected function getWaitingListInstance()
	{
		$crs_id = $this->getCourse()->getId();
		
		if(!array_key_exists($crs_id, self::$waiting_instances))
		{
			require_once "Modules/Course/classes/class.ilCourseWaitingList.php";
			self::$waiting_instances[$crs_id] = new ilCourseWaitingList($crs_id);
		}
		
		return self::$waiting_instances[$crs_id];
	}
	
	
	//
	// properties
	//
	
	/**
	 * Set course
	 * 
	 * @param ilObjCourse $a_course
	 */
	protected function setCourse(ilObjCourse $a_course)
	{
		$this->course = $a_course;
	}
	
	/**
	 * Get course
	 * 
	 * @return ilObjCourse 
	 */	
	protected function getCourse()
	{
		$this->gLog->write("get Course");
		return $this->course;
	}
	
	
	//
	// course status
	//
	
	/**
	 * Is waiting list active?
	 *
	 * @return bool
	 */
	public function isWaitingListActivated()
	{
		return ($this->getCourse()->isSubscriptionMembershipLimited() &&
			$this->getCourse()->enabledWaitingList());
	}
	
	/**
	 * Get number of free places
	 * 
	 * @return int
	 */
	public function getFreePlaces()
	{
		$max = $this->getCourse()->getSubscriptionMaxMembers();
				
		if ($this->getCourse()->isSubscriptionMembershipLimited() && $max)
		{			
			return max(0, $max - $this->getCourse()->getMembersObject()->getCountMembers());
		}				
	}
	
	/**
	 * Get course booking deadline
	 * 
	 * @return ilDate
	 */
	public function getBookingDeadline()
	{
		return ilCourseBookingHelper::getInstance($this->getCourse())->getBookingDeadline();
	}
	
	/**
	 * Get course cancellation deadline
	 * 
	 * @return ilDate
	 */
	public function getCancellationDeadline()
	{
		return ilCourseBookingHelper::getInstance($this->getCourse())->getCancellationDeadline();
	}

	
	//
	// course actions
	//
	
	/**
	 * Fill free places from waiting list	
	 * 
	 * @return bool
	 */
	public function fillFreePlaces()
	{
		if($this->isWaitingListActivated())
		{
			$free = $this->getFreePlaces();
			if($free)
			{				
				$waiting_status = $this->getWaitingUsers();
				
				$wlist = $this->getWaitingListInstance();
				foreach($wlist->getUserIds() as $user_id)
				{
					// check against booking status
					if(!in_array($user_id, $waiting_status))
					{
						$wlist->removeFromList($user_id);
						continue;
					}
					
					// :TODO: join() ?! 
					$this->bookCourse($user_id);					
					$free--;					
					
					if(!$free)
					{
						break;
					}
				}			
				return true;
			}
		}
		return false;
	}
	
	// gev-patch start
	public function cleanWaitingList()
	{
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		
		$automails = new gevCrsAutoMails($this->getCourse()->getId());
		
		if($this->isWaitingListActivated())
		{
			$waiting_status = $this->getWaitingUsers();
			
			$wlist = $this->getWaitingListInstance();
			foreach($wlist->getUserIds() as $user_id)
			{
				// check against booking status
				if(!in_array($user_id, $waiting_status))
				{
					$wlist->removeFromList($user_id);
					continue;
				}
				
				$this->cancelWithoutCosts($user_id);					
				$automails->send("waiting_list_cancelled", array($user_id));
			}
			return true;
		}
		return false;
	}
	// gev-patch end
	
	//
	// users status 
	//
	
	/**
	 * Get user ids by status
	 * 
	 * @param int|array $a_status
	 * @return array
	 */
	protected function getUsersByStatus($a_status)
	{
		$crs_id = $this->getCourse()->getId();
		$res = ilCourseBooking::getUsersByStatus($crs_id, $a_status, true);
		
		// internal caching
		if(is_array($res))
		{
			foreach($res as $user_id => $status)
			{
				self::$user_status[$crs_id][$user_id] = $status;
			}	
			
			$res = array_keys($res);
		}
		
		return $res;		
	}
		
	/**
	 * Get all proper members
	 * 
	 * @return array
	 */
	public function getBookedUsers()
	{
		return $this->getUsersByStatus(ilCourseBooking::STATUS_BOOKED);
	}
	
	/**
	 * Get all waiting list users
	 * 
	 * @return array
	 */
	public function getWaitingUsers()
	{
		return $this->getUsersByStatus(ilCourseBooking::STATUS_WAITING);
	}
	
	/**
	 * Get all proper members and waiting list users
	 * 
	 * @return array
	 */
	public function getBookedAndWaitingUsers()
	{
		return $this->getUsersByStatus(array(ilCourseBooking::STATUS_BOOKED, 
			ilCourseBooking::STATUS_WAITING));
	}
	
	/**
	 * Get all cancelled with costs users
	 * 
	 * @return array
	 */
	public function getCancelledWithCostsUsers()
	{
		return $this->getUsersByStatus(ilCourseBooking::STATUS_CANCELLED_WITH_COSTS);
	}
	
	/**
	 * Get all cancelled without costs users
	 * 
	 * @return array
	 */
	public function getCancelledWithoutCostsUsers()
	{
		return $this->getUsersByStatus(ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS);
	}
	
	/**
	 * Get all cancelled users (regardless of cost)
	 * 
	 * @return array
	 */
	public function getCancelledUsers()
	{
		return $this->getUsersByStatus(array(ilCourseBooking::STATUS_CANCELLED_WITH_COSTS, 
			ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS));
	}
	
		
	//
	// user status
	//
	
	/**
	 * Get user status
	 * 
	 * @param int $a_user_id
	 * @return int
	 */
	public function getUserStatus($a_user_id)
	{
		$this->gLog->write("Search booking status");
		$crs_id = $this->getCourse()->getId();
		$this->gLog->write("Search status for crs id ".$crs_id);
		if(!isset(self::$user_status[$crs_id][$a_user_id]))
		{
			self::$user_status[$crs_id][$a_user_id] = 
				ilCourseBooking::getUserStatus($crs_id, $a_user_id);
		}
		$this->gLog->write("State: ".self::$user_status[$crs_id][$a_user_id]);
		$this->gLog->write("Search booking status finished");
		return self::$user_status[$crs_id][$a_user_id];
	}
	
	/**
	 * Is user already member?
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function isMember($a_user_id)
	{
		return ($this->getUserStatus($a_user_id) == ilCourseBooking::STATUS_BOOKED);
	}
	
	/**
	 * Is user on waiting list?
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function isWaiting($a_user_id)
	{
		return ($this->getUserStatus($a_user_id) == ilCourseBooking::STATUS_WAITING);
	}
	
	/**
	 * Is user already member or on waiting list?
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function isMemberOrWaiting($a_user_id)
	{
		$status = $this->getUserStatus($a_user_id);
		return in_array($status, array(ilCourseBooking::STATUS_BOOKED, 
			ilCourseBooking::STATUS_WAITING));
	}
	
	/**
	 * Is user cancelled?
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function isCancelled($a_user_id)
	{
		$status = $this->getUserStatus($a_user_id);
		return in_array($status, array(ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS, 
			ilCourseBooking::STATUS_CANCELLED_WITH_COSTS));
	}
	
	
	//
	// user action
	//
	
	/**
	 * Update user status
	 * 
	 * @param int $a_user_id
	 * @param int $a_status
	 * @return boolean
	 */
	protected function updateUserStatus($a_user_id, $a_status)
	{
		$crs_id = $this->getCourse()->getId();
		if(ilCourseBooking::setUserStatus($crs_id, $a_user_id, $a_status))
		{
			self::$user_status[$crs_id][$a_user_id] = $a_status;
			return true;
		}
		return false;
	}
			
	/**
	 * Make user (proper) course member
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function bookCourse($a_user_id)
	{
		$status = $this->getUserStatus($a_user_id);
		
		if($status == ilCourseBooking::STATUS_BOOKED)
		{
			$this->gLog->write("State equals BOOKED");
			return true;
		}
		
		/* see ilParticipant::add()
		if($status == ilCourseBooking::STATUS_WAITING)
		{
			$wlist = $this->getWaitingListInstance();
			$wlist->removeFromList($a_user_id);
		}		
		*/		

		$this->gLog->write("Try to add user to course (role assignment)");
		if($this->getCourse()->getMemberObject()->add($a_user_id, IL_CRS_MEMBER))
		{
			return $this->updateUserStatus($a_user_id, ilCourseBooking::STATUS_BOOKED);
		}
		
		return false;
	}
	
	/**
	 * Put user on waiting list
	 * 
	 * @throws ilException
	 * @param int $a_user_id
	 * @return bool
	 */
	public function putOnWaitingList($a_user_id)
	{
		if(!$this->isWaitingListActivated())
		{
			throw new ilException("CourseBooking: cannot put member on inactive waiting list");
		}
		
		$status = $this->getUserStatus($a_user_id);				
		
		if($status == ilCourseBooking::STATUS_WAITING)
		{
			return true;
		}
		
		// :TODO: cannot be member AND waiting?
		if($status == ilCourseBooking::STATUS_BOOKED)
		{
			$this->getCourse()->getMemberObject()->delete($a_user_id);
		}
		
		if($this->updateUserStatus($a_user_id, ilCourseBooking::STATUS_WAITING))
		{		
			$wlist = $this->getWaitingListInstance();
			$wlist->addToList($a_user_id);
			return true;
		}
		
		return false;
	}
	
	/**
	 * Cancel user (with/without costs)
	 * 
	 * @throws ilException
	 * @param int $a_user_id
	 * @param bool $a_with_costs
	 * @return bool	 
	 */
	protected function cancelUser($a_user_id, $a_with_costs = false)
	{		
		switch($this->getUserStatus($a_user_id))
		{
			case ilCourseBooking::STATUS_CANCELLED_WITH_COSTS:
			case ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS:
				return true;
				
			case ilCourseBooking::STATUS_WAITING:
				$wlist = $this->getWaitingListInstance();
				$wlist->removeFromList($a_user_id);				
				break;
			
			case ilCourseBooking::STATUS_BOOKED:
				$this->getCourse()->getMemberObject()->delete($a_user_id);
				break;				
		
			default:				
				throw new ilException("CourseBooking: cannot cancel non-member");				
		}
		
		$status = (bool)$a_with_costs 
			? ilCourseBooking::STATUS_CANCELLED_WITH_COSTS
			: ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS;
		
		// gev-patch start
		// This is not done via ilAccomodationsAppEventHandler to avoid removal of
		// overnights set for people who are moved to the waiting list.
		require_once("Services/Accomodations/classes/class.ilAccomodations.php");
		$accomodations = ilAccomodations::getInstance($this->getCourse());
		$accomodations->deleteAccomodations($a_user_id);
		// gev-patch end
		
		return $this->updateUserStatus($a_user_id, $status);
	}
		
	/**
	 * Cancel user membership with costs
	 * 
	 * @throws ilException
	 * @param int $a_user_id
	 * @return bool
	 */
	public function cancelWithCosts($a_user_id)
	{
		return $this->cancelUser($a_user_id, true);
	}
	
	/**
	 * Cancel user membership without costs
	 * 
	 * @param int $a_user_id
	 */
	public function cancelWithoutCosts($a_user_id)
	{
		return $this->cancelUser($a_user_id);
	}
	
	/**
	 * Add member depending on course status
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function join($a_user_id)
	{
		$now = new ilDate(date("Y-m-d"), IL_CAL_DATE);
		$deadline = $this->getBookingDeadline();
		if($deadline && ilDate::_after($now, $deadline))
		{
			return false;
		}
		
		$status = $this->getUserStatus($a_user_id);
		
		if($status == ilCourseBooking::STATUS_BOOKED)
		{
			return true;
		}
						
		$free = $this->getFreePlaces();
		if($free === null || (int)$free)
		{
			return $this->bookCourse($a_user_id);			
		}
		else if($this->isWaitingListActivated())
		{				
			return $this->putOnWaitingList($a_user_id);			
		}
		
		return false;	
	}
	
	/**
	 * Remove member depending on course/member status
	 * 
	 * @throws ilException
	 * @param int $a_user_id
	 * @return bool
	 */
	public function cancel($a_user_id)
	{
		switch($this->getUserStatus($a_user_id))
		{
			case ilCourseBooking::STATUS_CANCELLED_WITH_COSTS:
			case ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS:
				return true;
			
			case ilCourseBooking::STATUS_WAITING:
				return $this->cancelWithoutCosts($a_user_id);
				
			case ilCourseBooking::STATUS_BOOKED:
				$now = new ilDate(time(), IL_CAL_UNIX);
				$deadline = $this->getCancellationDeadline();
				
				// gev-patch start (2014-10-01)
				require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
				require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
				require_once("Services/GEV/Utils/classes/class.gevBillingUtils.php");
				
				$crs_utils = gevCourseUtils::getInstanceByObj($this->course);
				$usr_utils = gevUserUtils::getInstance($a_user_id);

				$crs_reached_deadline = ($deadline !== null && ilDate::_after($now, $deadline));
				$crs_hasfee = $crs_utils->getFee();
				$usr_paysfee = $usr_utils->paysFees();
				$usr_has_bill = gevBillingUtils::getInstance()->getNonFinalizedBillForCourseAndUser($this->course->getId(), $a_user_id) !== null;
												
				if($crs_reached_deadline && $crs_hasfee && $usr_paysfee && $usr_has_bill){
					return $this->cancelWithCosts($a_user_id);
				} else {
					return $this->cancelWithoutCosts($a_user_id);
				}
				// gev-patch end (2014-10-01)



			default:
				throw new ilException("CourseBooking: cannot cancel non-member");							
		}
	}
}
