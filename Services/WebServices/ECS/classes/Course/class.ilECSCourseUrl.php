<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseUrlConnector.php';

/**
 * Represents a ecs course url
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSCourseUrl
{
	const COURSE_URL_PREFIX = 'campusconnect/course/';

	// json fields
	public $cms_lecture_id = '';
	public $ecs_course_url = '';
	public $lms_course_urls = null;
	
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * Set lecture id
	 * @param type $a_id
	 */
	public function setCmsLectureId($a_id)
	{
		$this->cms_lecture_id = $a_id;
	}
	
	/**
	 * Set ecs course id
	 * @param int $a_id
	 */
	public function setECSId($a_id)
	{
		$this->ecs_course_url = self::COURSE_URL_PREFIX.$a_id;
	}
	
	/**
	 * Add lms url
	 * @param ilECSCourseLmsUrl $lms_url
	 */
	public function addLmsCourseUrls(ilECSCourseLmsUrl $lms_url = null)
	{
		$this->lms_course_urls[] = $lms_url;
	}
	
	/**
	 * Send urls to ecs
	 */
	public function send(ilECSSetting $setting, $ecs_receiver_mid)
	{
		include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseUrlConnector.php';
		try
		{
			$con = new ilECSCourseUrlConnector($setting);
			$url_id = $con->addUrl($this, $ecs_receiver_mid);
			
			$GLOBALS['ilLog']->write(__METHOD__.': Received new url id ' . $url_id);
		}
		catch(Exception $e)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': '.$e->getMessage());
		}
	}
}
?>