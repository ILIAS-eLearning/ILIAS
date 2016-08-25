<?php

namespace CaT\Plugins\CareerGoal\Observations;

class Observation {
	/**
	 * @var int
	 */
	protected $obj_id;

	/**
	 * @var int
	 */
	protected $career_goal_id;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var int
	 */
	protected $position;

	/**
	 * @var array
	 */
	protected $requirements;

	public function __construct($obj_id, $career_goal_id, $title, $description, $position, array $requirements) {
		assert('is_int($obj_id)');
		$this->obj_id = $obj_id;
		assert('is_int($career_goal_id)');
		$this->career_goal_id = $career_goal_id;
		assert('is_string($title)');
		$this->title = $title;
		assert('is_string($description)');
		$this->description = $description;
		assert('is_int($position)');
		$this->position = $position;
		$this->requirements = $requirements;
	}

	public function withTitle($title) {
		assert('is_string($title)');
		$clone = clone $this;
		$clone->title = $title;

		return $clone;
	}

	public function withDescription($description) {
		assert('is_string($description)');
		$clone = clone $this;
		$clone->description = $description;

		return $clone;
	}

	public function withPosition($position) {
		assert('is_int($position)');
		$clone = clone $this;
		$clone->position = $position;

		return $clone;
	}

	public function withRequirements(array $requirements) {
		$clone = clone $this;
		$clone->requirements = $requirements;

		return $clone;
	}

	public function getObjId() {
		return $this->obj_id;
	}

	public function getCareerGoalId() {
		return $this->career_goal_id;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getDescription() {
		return $this->description;
	}

	public function getPosition() {
		return $this->position;
	}

	public function getRequirements() {
		return $this->requirements;
	}
}