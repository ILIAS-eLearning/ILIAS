<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * This is the object for a log entry
 */
class LogEntry {

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var ilDateTime
	 */
	protected $date;

	/**
	 * @var string
	 */
	protected $event;

	/**
	 * @var int|null
	 */
	protected $crs_ref_id;

	/**
	 * @var string
	 */
	protected $template_ident;

	/**
	 * @var int | null
	 */
	protected $usr_id;

	/**
	 * @var string
	 */
	protected $usr_login;

	/**
	 * @var string
	 */
	protected $usr_mail;

	/**
	 * @var string
	 */
	protected $usr_name;

	/**
	 * @var string
	 */
	protected $subject;
	/**
	 * @var string
	 */
	protected $msg;

	/**
	 * @var string
	 */
	protected $error;


	/**
	 * @param int 	$id
	 * @param ilDateTime  	$date
	 * @param string  	$event
	 * @param int|null  	$crs_ref_id
	 * @param string  	$template_ident
	 * @param int |null 	$usr_id
	 * @param string | null  	$usr_login
	 * @param string | null  	$usr_name
	 * @param string  	$usr_mail
	 * @param string  	$subject
	 * @param string  	$msg
	 * @param string  	$error
	 */
	public function __construct($id, \ilDateTime $date,
			$event, $crs_ref_id, $template_ident,
			$usr_id, $usr_login, $usr_name, $usr_mail,
			$subject = '', $msg = '', $error='') {

		assert('is_int($id)');
		assert('is_string($event)');
		assert('is_int($crs_ref_id) || $crs_ref_id===null');
		assert('is_string($template_ident)');
		assert('is_int($usr_id) || $usr_id===null');
		assert('is_string($usr_login) || $usr_login===null');
		assert('is_string($usr_name) || $usr_name===null');
		assert('is_string($usr_mail)');
		assert('is_string($subject)');
		assert('is_string($msg)');
		assert('is_string($error)');

		$this->id = $id;
		$this->date = $date;
		$this->event = $event;
		$this->crs_ref_id = $crs_ref_id;
		$this->template_ident = $template_ident;
		$this->usr_id = $usr_id;
		$this->usr_login = $usr_login;
		$this->usr_name = $usr_name;
		$this->usr_mail = $usr_mail;
		$this->subject = $subject;
		$this->msg = $msg;
		$this->error = $error;
	}

	/**
	* @return int
	*/
	public function getId() {
		return $this->id;
	}

	/**
	* @return \ilDateTime
	*/
	public function getDate() {
		return $this->date;
	}

	/**
	* @return string
	*/
	public function getDateAsString() {
		return $this->date->get(IL_CAL_DATETIME);
	}

	/**
	* @return \string
	*/
	public function getEvent() {
		return $this->event;
	}

	/**
	* @return int|null
	*/
	public function getCourseRefId() {
		return $this->crs_ref_id;
	}

	/**
	* @return string
	*/
	public function getTemplateIdent() {
		return $this->template_ident;
	}

	/**
	* @return int
	*/
	public function getUserId() {
		return $this->usr_id;
	}

	/**
	* @return string
	*/
	public function getUserLogin() {
		return $this->usr_login;
	}

	/**
	* @return string
	*/
	public function getUserName() {
		return $this->usr_name;
	}

	/**
	* @return string
	*/
	public function getUserMail() {
		return $this->usr_mail;
	}

	/**
	* @return string
	*/
	public function getSubject() {
		return $this->subject;
	}

	/**
	* @return string
	*/
	public function getMessage() {
		return $this->msg;
	}

	/**
	* @return string
	*/
	public function getError() {
		return $this->error;
	}

}
