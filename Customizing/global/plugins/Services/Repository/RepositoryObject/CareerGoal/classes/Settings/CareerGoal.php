<?php
namespace CaT\Plugins\CareerGoal\Settings;

class CareerGoal {
	/**
	 * @var int
	 */
	protected $obj_id;

	/**
	 * @var float
	 */
	protected $lowmark;

	/**
	 * @var float
	 */
	protected $should_specifiaction;

	/**
	 * @var string
	 */
	protected $default_text_failed;

	/**
	 * @var string
	 */
	protected $default_text_partial;

	/**
	 * @var string
	 */
	protected $default_text_success;

	public function __construct($obj_id, $lowmark, $should_specifiaction, $default_text_failed, $default_text_partial, $default_text_success) {
		assert('is_int($obj_id)');
		$this->obj_id = $obj_id;

		assert('is_int($lowmark) || is_float($lowmark)');
		$this->lowmark = $lowmark;

		assert('is_int($should_specifiaction) || is_float($should_specifiaction)');
		$this->should_specifiaction = $should_specifiaction;

		assert('is_string($default_text_failed)');
		$this->default_text_failed = $default_text_failed;

		assert('is_string($default_text_failed)');
		$this->default_text_partial = $default_text_partial;

		assert('is_string($default_text_failed)');
		$this->default_text_success = $default_text_success;
	}

	public function withLowmark($lowmark) {
		assert('is_int($lowmark) || is_float($lowmark)');

		$clone = clone $this;
		$clone->lowmark = $lowmark;

		return $clone;
	}

	public function withShouldSpecifiaction($should_specifiaction) {
		assert('is_int($should_specifiaction) || is_float($should_specifiaction)');

		$clone = clone $this;
		$clone->should_specifiaction = $should_specifiaction;

		return $clone;
	}

	public function withDefaultTextFailed($default_text_failed) {
		assert('is_string($default_text_failed)');

		$clone = clone $this;
		$clone->default_text_failed = $default_text_failed;

		return $clone;
	}

	public function withDefaultTextPartial($default_text_partial) {
		assert('is_string($default_text_partial)');

		$clone = clone $this;
		$clone->default_text_partial = $default_text_partial;

		return $clone;
	}

	public function withDefaultTextSuccess($default_text_success) {
		assert('is_string($default_text_success)');

		$clone = clone $this;
		$clone->default_text_success = $default_text_success;

		return $clone;
	}

	public function getObjId() {
		return $this->obj_id;
	}

	public function getLowmark() {
		return $this->lowmark;
	}

	public function getShouldSpecification() {
		return $this->should_specifiaction;
	}

	public function getDefaultTextFailed() {
		return $this->default_text_failed;
	}

	public function getDefaultTextPartial() {
		return $this->default_text_partial;
	}

	public function getDefaultTextSuccess() {
		return $this->default_text_success;
	}
}