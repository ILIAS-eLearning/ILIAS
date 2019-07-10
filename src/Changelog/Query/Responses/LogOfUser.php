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
	public $acting_user_login;

	/**
	 * @var string
	 */
	public $member_login;

	/**
	 * @var string
	 */
	public $event_type_title;


}