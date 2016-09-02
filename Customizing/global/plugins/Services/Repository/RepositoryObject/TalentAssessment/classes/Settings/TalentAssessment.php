<?php

namespace CaT\Plugins\TalentAssessment\Settings;

class TalentAssessment {
	const IN_PROGRESS = "1";
	const FINISHED = "2";

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

	/**
	 * @var boolean
	 */
	protected $started;

	/**
	 * @var float
	 */
	protected $lowmark;

	/**
	 * @var float
	 */
	protected $should_specifiaction;

	/**
	 * @var float
	 */
	protected $potential;

	/**
	 * @var string
	 */
	protected $result_comment;

	public function __construct($obj_id, $state, $career_goal_id, $username, $firstname, $lastname, $email, $start_date
								, $end_date, $venue, $org_unit, $started, $lowmark, $should_specifiaction
								, $potential, $result_comment) {
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
		assert('is_bool($started)');
		$this->started = $started;
		assert('is_float($lowmark)');
		$this->lowmark = $lowmark;
		assert('is_float($should_specifiaction)');
		$this->should_specifiaction = $should_specifiaction;
		assert('is_float($potential)');
		$this->potential = $potential;
		assert('is_string($result_comment)');
		$this->result_comment = $result_comment;

		$this->state = $state;
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

	public function withUsername($username) {
		assert('is_string($username)');
		$clone = clone $this;
		$clone->username = $username;

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

	public function withStarted($started) {
		assert('is_bool($started)');
		$clone = clone $this;
		$clone->started = $started;

		return $clone;
	}

	public function withLowmark($lowmark) {
		assert('is_float($lowmark)');
		$clone = clone $this;
		$clone->lowmark = $lowmark;

		return $clone;
	}

	public function withShouldSpecifiaction($should_specifiaction) {
		assert('is_float($should_specifiaction)');
		$clone = clone $this;
		$clone->should_specifiaction = $should_specifiaction;

		return $clone;
	}

	public function withPotential($potential) {
		assert('is_float($potential)');
		$clone = clone $this;
		$clone->potential = $potential;

		return $clone;
	}

	public function withResultComment($result_comment) {
		assert('is_string($result_comment)');
		$clone = clone $this;
		$clone->result_comment = $result_comment;

		return $clone;
	}

	public function withFinished($finished) {
		if($finished) {
			$clone = clone $this;
			$clone->state = self::FINISHED;

			return $clone;
		}

		return $this;
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

	public function getStarted() {
		return $this->started;
	}

	public function getLowmark() {
		return $this->lowmark;
	}

	public function getShouldSpecification() {
		return $this->should_specifiaction;
	}

	public function getPotential() {
		return $this->potential;
	}

	public function getResultComment() {
		return $this->result_comment;
	}

	public function Finished() {
		return $this->state == self::FINISHED;
	}
}