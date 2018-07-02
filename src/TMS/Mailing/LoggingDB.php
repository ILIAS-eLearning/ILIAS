<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * Interface for DB handle of Mail-Logs
 */
interface LoggingDB {
	/**
	 * Create a new log entry
	 *
	 * @param string  	$event
	 * @param string  	$template_ident
	 * @param string  	$usr_mail
	 * @param string  	$usr_name
	 * @param int|null  	$usr_id
	 * @param string  	$usr_login
	 * @param int|null  	$crs_ref_id
	 * @param string  	$subject
	 * @param string  	$msg
	 * @param string  	$error
	 *
	 * @return LogEntry
	 */
	public function log($event, $template_ident, $usr_mail,
		$usr_name = '', $usr_id = null, $usr_login = '',
		$crs_ref_id = null, $subject = '', $msg = '', $error='');

	/**
	 * Get logs for course's ref_id
	 *
	 * @param int $ref_id
	 * @param string[]|null $sort 	array(field, "asc"|"desc")|null
	 * @param int[]|null $limit 	array(length, offset)|null
	 *
	 * @return LogEntry[]
	 */
	public function	selectForCourse($ref_id, $sort=null, $limit=null);

	/**
	 * Get number of entries for course.
	 *
	 * @param int $ref_id
	 *
	 * @return int
	 */
	public function	selectCountForCourse($ref_id);


}
