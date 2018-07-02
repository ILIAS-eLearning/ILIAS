<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Encapsulates information about one request for a course creation.
 */
class Request {
	/**
	 * @var	int
	 */
	protected $id;

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var string
	 */
	public $session_id;

	/**
	 * @var	int
	 */
	protected $crs_ref_id;

	/**
	 * @var	int
	 */
	protected $new_parent_ref_id;

	/**
	 * @var array<int,int>
	 */
	protected $copy_options;

	/**
	 * @var array<int,mixed>
	 */
	protected $configuration;

	/**
	 * @var \DateTime 
	 */
	protected $requested_ts;

	/**
	 * @var	int|null
	 */
	protected $target_ref_id;

	/**
 	 * @var \DateTime|null
	 */
	protected $finished_ts;

	/**
 	 * @var int		$id
 	 * @var int		$user_id
 	 * @var string	$session_id
 	 * @var int		$crs_ref_id
	 */
	public function __construct(
			$id,
			$user_id,
			$session_id,
			$crs_ref_id,
			$new_parent_ref_id,
			array $copy_options,
			array $configuration,
			\DateTime $requested_ts,
			$target_ref_id = null,
			\DateTime $finished_ts = null
		) {
		assert('is_int($id)');
		assert('is_int($user_id)');
		assert('is_string($session_id)');
		assert('is_int($crs_ref_id)');
		assert('is_int($new_parent_ref_id)');
		assert('is_int($target_ref_id) || is_null($target_ref_id)');
		$this->id = $id;
		$this->user_id = $user_id;
		$this->session_id = $session_id;
		$this->crs_ref_id = $crs_ref_id;
		$this->new_parent_ref_id = $new_parent_ref_id;
		$this->copy_options = $copy_options;
		foreach ($this->copy_options as $k => $v) {
			assert('is_int($k)');
			assert('$v === 1 || $v === 2 || $v === 3');
		}
		$this->configuration = $configuration;
		foreach ($this->configuration as $k => $v) {
			assert('is_int($k)');
			assert('is_string(json_encode($v))');
		}
		$this->requested_ts = $requested_ts;
		$this->target_ref_id = $target_ref_id;
		$this->finished_ts = $finished_ts;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * @return var 
	 */
	public function getSessionId() {
		return $this->session_id;
	}

	/**
	 * @return int
	 */
	public function getCourseRefId() {
		return $this->crs_ref_id;
	}

	/**
	 * @return int
	 */
	public function getNewParentRefId() {
		return $this->new_parent_ref_id;
	}

	/**
	 * @return \DateTime 
	 */
	public function getRequestedTS() {
		return $this->requested_ts;
	}

	/**
	 * @return int|null
	 */
	public function getTargetRefId() {
		return $this->target_ref_id;
	}

	/**
	 * @return \DateTime
	 */
	public function getFinishedTS() {
		return $this->finished_ts;
	}

	/**
	 * @param	int			$target_ref_id
	 * @param	\DateTime	$finished_ts
	 * @return self
	 */
	public function withTargetRefIdAndFinishedTS($target_ref_id, \DateTime $finished_ts) {
		assert('is_int($target_ref_id)');
		$clone = clone $this;
		$clone->target_ref_id = $target_ref_id;
		$clone->finished_ts = $finished_ts;
		return $clone;
	}

	/**
	 * @param	\DateTime	$finished_ts
	 * @return self
	 */
	public function withFinishedTS(\DateTime $finished_ts) {
		$clone = clone $this;
		$clone->finished_ts = $finished_ts;
		return $clone;
	}

	// TODO: there is a hidden dependency on ilCopyWizardOption here
	const SKIP = 1;
	const COPY = 2;
	const LINK = 3;

	/**
	 * Get copy setting for the object with the given ref_id.
	 *
	 * Defaults to skip.
	 *
	 * @param	int		$ref_id
	 * @return 	mixed
	 */
	public function getCopyOptionFor($ref_id) {
		if (!isset($this->copy_options[$ref_id])) {
			return self::SKIP;
		}
		return $this->copy_options[$ref_id];
	}

    /**
     * Get all copy options.
     *
     * @return array<int,int>
     */
    public function getCopyOptions() {
        return $this->copy_options;
    }

	/**
	 * Get the configuration data for the object with the given ref_id.
	 *
	 * Defaults to null.
	 *
	 * @param	int	$ref_id
	 * @return	mixed[]|null
	 */
	public function getConfigurationFor($ref_id) {
		if (!isset($this->configuration[$ref_id])) {
			return null;
		}
		return $this->configuration[$ref_id];
	}

    /**
     * Get all configurations.
     *
     * @return array<int,int>
     */
    public function getConfigurations() {
        return $this->configuration;
    }
}
