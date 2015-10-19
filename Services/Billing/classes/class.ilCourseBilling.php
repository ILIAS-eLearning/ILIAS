<?php

require_once 'Services/CourseBooking/classes/class.ilCourseBooking.php';

class ilCourseBilling {
/**
	 * Get complete course booking data and bills for table GUI
	 * 
	 * @param int $a_course_obj_id
	 * @param bool $a_show_cancellations
	 * @param int $a_offset
	 * @param int $a_limit
	 * @return array
	 */
	 protected $course;
	 protected static $instance;

	protected function __construct($a_course_obj) {
		global $ilDB;
		$this->gIldb = $ilDB; 
		$this->course = $a_course_obj;
	}

	public static function getInstance($a_course_obj) {
		if(!self::$instance) {
			self::$instance = new self($a_course_obj);
		}
		return self::$instance;
	}

	public function getCourseTableData($a_offset, $a_limit)
	{
		$res = array();
			
		$sql = 	"SELECT  usr.firstname AS firstname, usr.lastname AS lastname, usr.login AS login, usr.email AS email,"
				."	crb.status, crb.crs_id, crb.user_id, bill.bill_pk, "
				."	GROUP_CONCAT( DISTINCT ou_title.title ORDER BY ou_title.title SEPARATOR ', ' ) AS orgutitle"
				."	FROM crs_book crb"
				."	JOIN usr_data usr" 
				."		ON usr.usr_id = crb.user_id"
				."	JOIN rbac_ua ua"
				."		ON ua.usr_id = crb.user_id"
				."	LEFT JOIN object_data role_data"
				."		ON role_data.obj_id = ua.rol_id AND ( role_data.title LIKE 'il_orgu_superior_%' OR role_data.title LIKE 'il_orgu_employee_%')"
				."	LEFT JOIN object_reference ou_ref" 
				."		ON ou_ref.ref_id = SUBSTRING(role_data.title, 18)"
				."	LEFT JOIN object_data ou_title" 
				."		ON ou_title.obj_id = ou_ref.obj_id"
				."	LEFT JOIN bill"
				."		ON bill.bill_context_id = crb.crs_id AND bill.bill_usr_id = usr.usr_id"
				."	WHERE crb.crs_id = ". $this->gIldb->quote($this->course->getId(), "integer")
				."		AND ". $this->gIldb->in("crb.status ",array(ilCourseBooking::STATUS_CANCELLED_WITH_COSTS,ilCourseBooking::STATUS_BOOKED),false,"text")
				."	GROUP BY usr.firstname, usr.lastname, usr.login, crb.status, crb.crs_id, crb.user_id";

		if($a_limit != 0) {
			if($a_offset === null) {
				$a_offset = 0;
			}

			$sql .= " LIMIT ".$a_offset.", ".$a_limit."";
		}

		$res = array();
		$set = $this->gIldb->query($sql);
		while($row =  $this->gIldb->fetchAssoc($set)) {
			$res[] = $row;
		}
		
		return $res;
	}

	public function userMayHaveBill(gevUserUtils $a_user_utils) {
		if(!$a_user_utils->paysFees()) {
			return false;
		}

		$sql = 	"SELECT  crb.user_id "
				."	FROM crs_book crb"
				."	WHERE crb.crs_id = ".$this->gIldb->quote($this->course->getId(), "integer")
				."		AND crb.user_id = ".$this->gIldb->quote($a_user_utils->getId(), "integer")
				."		AND ". $this->gIldb->in("crb.status ",array(ilCourseBooking::STATUS_CANCELLED_WITH_COSTS,ilCourseBooking::STATUS_BOOKED),false,"text");

		$set = $this->gIldb->query($sql);

		while($row =  $this->gIldb->fetchAssoc($set)) {
			return true;
		}
		return false;
	}
}