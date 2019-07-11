<?php

namespace ILIAS\Changelog\Query\Responses;


use ilDateTime;

/**
 * Class LogOfCourse
 * @package ILIAS\Changelog\Query\Responses
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class LogOfCourse {

	/**
	 * @var ilDateTime
	 */
	public $date;

	/**
	 * @var int
	 */
	public $crs_obj_id;

	/**
	 * @var string
	 */
	public $hist_crs_title;

	/**
	 * @var string
	 */
	public $acting_user_id;

	/**
	 * @var string
	 */
	public $acting_user_firstname;

	/**
	 * @var string
	 */
	public $acting_user_lastname;

	/**
	 * @var string
	 */
	public $acting_user_login;

	/**
	 * @var int
	 */
	public $member_user_id;

	/**
	 * @var string
	 */
	public $member_login;

	/**
	 * @var string
	 */
	public $member_firstname;

	/**
	 * @var string
	 */
	public $member_lastname;

	/**
	 * @var string
	 */
	public $event_title_lang_var;

	/**
	 * @var int
	 */
	public $event_type_id;
}