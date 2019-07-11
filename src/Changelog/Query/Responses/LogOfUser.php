<?php

namespace ILIAS\Changelog\Query\Responses;


use ilDateTime;

/**
 * Class LogOfUser
 * @package ILIAS\Changelog\Query\Responses
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class LogOfUser {

	/**
	 * @var ilDateTime
	 */
	public $date;

	/**
	 * @var string
	 */
	public $acting_user_id;

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
	public $event_title;

	/**
	 * @var int
	 */
	public $event_type_id;


}