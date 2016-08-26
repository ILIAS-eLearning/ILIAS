<?php

namespace CaT\Plugins\TalentAssessment\Settings;

class TalentAssessment {
	const IN_PROGRESS = "1";

	/**
	 * @var int
	 */
	protected $obj_id;

	/**
	 * @var string
	 */
	protected $state;

	/**
	 * @var int
	 */
	protected $career_goal_id;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $firstname;

	/**
	 * @var string
	 */
	protected $lastname;

	/**
	 * @var string
	 */
	protected $email;

	/**
	 * @var ilDateTime
	 */
	protected $start_date;

	/**
	 * @var ilDateTime
	 */
	protected $end_date;

	/**
	 * @var int
	 */
	protected $venue;

	/**
	 * @var int
	 */
	protected $org_unit;

	public function __construct($obj_id, $state, $career_goal_id, $username, $firstname, $lastname, $email, $start_date, $end_date, $venue, $org_unit) {
		assert('is_int($obj_id)');
		$this->obj_id = $obj_id;
		assert('is_int($career_goal_id)');
		$this->career_goal_id = $career_goal_id;
		assert('is_string($username)');
		$this->username = $username;
		assert('is_string($firstname)');
		$this->firstname = $firstname;
		assert('is_string($lastname)');
		$this->lastname = $lastname;
		assert('is_string($email)');
		$this->email = $email;
		$this->start_date = $start_date;
		$this->end_date = $end_date;
		assert('is_int($venue)');
		$this->venue = $venue;
		assert('is_int($org_unit)');
		$this->org_unit = $org_unit;

		$this->$state = $state;
	}

	public function withState($state) {
		$clone = clone $this;
		$clone->state = $state;

		return $clone;
	}

	public function withCareerGoalID($career_goal_id) {
		assert('is_int($career_goal_id)');
		$clone = clone $this;
		$clone->career_goal_id = $career_goal_id;

		return $clone;
	}

	public function withUserdata($username, $firstname, $lastname, $email) {
		assert('is_string($username)');
		assert('is_string($firstname)');
		assert('is_string($lastname)');
		assert('is_string($email)');
		$clone = clone $this;
		$clone->username = $username;
		$clone->firstname = $firstname;
		$clone->lastname = $lastname;
		$clone->email = $email;

		return $clone;
	}

	public function withStartDate(\ilDateTime $start_date) {
		$clone = clone $this;
		$clone->start_date = $start_date;

		return $clone;
	}

	public function withEndDate(\ilDateTime $end_date) {
		$clone = clone $this;
		$clone->end_date = $end_date;

		return $clone;
	}

	public function withVenue($venue) {
		assert('is_int($venue)');
		$clone = clone $this;
		$clone->venue = $venue;

		return $clone;
	}

	public function withOrgUnit($org_unit) {
		assert('is_int($org_unit)');
		$clone = clone $this;
		$clone->org_unit = $org_unit;

		return $clone;
	}

	public function getObjId() {
		return $this->obj_id;
	}

	public function getState() {
		return $this->state;
	}

	public function getCareerGoalId() {
		return $this->career_goal_id;
	}

	public function getUsername() {
		return $this->username;
	}

	public function getFirstname() {
		return $this->firstname;
	}

	public function getLastname() {
		return $this->lastname;
	}

	public function getEmail() {
		return $this->email;
	}

	public function getStartDate() {
		return $this->start_date;
	}

	public function getEndDate() {
		return $this->end_date;
	}

	public function getVenue() {
		return $this->venue;
	}

	public function getOrgUnit() {
		return $this->org_unit;
	}
}