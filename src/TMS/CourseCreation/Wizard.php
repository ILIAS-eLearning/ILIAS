<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

require_once(__DIR__."/../../../Services/Form/classes/class.ilFormSectionHeaderGUI.php");

use CaT\Ente\ILIAS\ilHandlerObjectHelper;

/**
 * Displays the steps for the creation of a course.
 */
class Wizard implements \ILIAS\TMS\Wizard\Wizard {
	use ilHandlerObjectHelper;

	const ID_BASE = "CourseCreation";

	/**
	 * @var	\ArrayAccess
	 */
	protected $dic;

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var	int
	 */
	protected $crs_ref_id;

	/**
	 * @var	ProcessStateDB
	 */
	protected $process_db;

	/**
	 * @var RequestBuilder
	 */
	protected $request_builder;

	/**
	 * @param	\ArrayAccess|array $dic
	 * @param	\RequestBuilder	$request_builder
	 * @param	string	$component_class	the user that performs the wizard 
	 * @param	int	$user_id				the user that performs the wizard
	 * @param	string	$session_id			session of said user
	 * @param	int	$crs_ref_id 			course that should get booked
	 * @param	int	$target_user_id			the user the booking is made for
	 * @param	int	$timestamp				timestamp the process was started
	 * TODO: turn timestamp to \DateTime, document it as member
	 */
	public function __construct($dic, RequestBuilder $request_builder, $user_id, $session_id, $crs_ref_id, $timestamp) {
		assert('is_array($dic) || ($dic instanceof \ArrayAccess)');
		assert('is_int($user_id)');
		assert('is_string($session_id)');
		assert('is_int($crs_ref_id)');
		assert('is_int($timestamp)');
		$this->request_builder = $request_builder;
		$this->dic = $dic;
		$this->user_id = $user_id;
		$this->session_id = $session_id;
		$this->crs_ref_id = $crs_ref_id;
		$this->timestamp = $timestamp;
	}

	/**
	 * @inheritdoc
	 */
	protected function getDIC() {
		return $this->dic;
	}

	/**
	 * @inheritdoc
	 */
	protected function getEntityRefId() {
		return $this->crs_ref_id;
	}

	/**
	 * Get the user that wants to create the course.
	 *
	 * @return	int
	 */
	protected function getUserId() {
		return $this->user_id;
	}

	/**
	 * Get the session id of the user that wants to create the course.
	 *
	 * @return string
	 */
	protected function getSessionId() {
		return $this->session_id;
	}

	/**
	 * Get the timestamp the user started the process.
	 *
	 * @return	int
	 */
	protected function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * Get the steps that are applicable for a given user.
	 *
	 * @return	Step[]
	 */
	protected function getApplicableSteps() {
		$steps = $this->getComponentsOfType(Step::class);
		return array_values(array_filter($steps, function($step) {
			$step->setUserId($this->getUserId());
			return $step->isApplicable();
		}));
	}

	/**
	 * Get the steps for the booking of the couse sorted by period.
	 *
	 * @return 	Step[]
	 */
	protected function getSortedSteps() {
		$steps = $this->getApplicableSteps();
		if (count($steps) === 0) {
			throw new \LogicException("No course creation steps defined.");
		}
		usort($steps, function (Step $a, Step $b) {
			if ($a->getPriority() < $b->getPriority()) {
				return -1;
			}
			if ($a->getPriority() > $b->getPriority()) {
				return 1;
			}
			return 0;
		});
		return $steps;
	}

	/**
	 * @inheritdoc
	 */
	public function getId() {
		return self::ID_BASE
			."_".$this->user_id
			."_".$this->crs_ref_id
			."_".$this->timestamp;
	}

	/**
	 * @inheritdoc
	 */
	public function getSteps() {
		$this->request_builder->setUserIdAndSessionId(
			$this->getUserId(),
			$this->getSessionId()
		);
		return array_map(function($s) {
			$s->setRequestBuilder($this->request_builder);
			return $s;
		}, $this->getSortedSteps());
	}


	/**
	 * @inheritdoc
	 */
	public function finish() {
		$this->request_builder->getRequest(new \DateTime());
	}
} 
