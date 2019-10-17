<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

use ILIAS\UI;

/**
 * A callable objective wraps a callable into an objective. 
 *
 * The callable receives the environment as parameter. It may return an updated
 * version of the environment, other results will be discarded.
 */
class CallableObjective implements Objective {
	/**
	 * @var callable
	 */
	protected $callable; 

	/**
	 * @var string 
	 */
	protected $label;

	/**
	 * @var bool
	 */
	protected $is_notable;

	/**
	 * @var	Objective[]
	 */
	protected $preconditions;

	public function __construct(callable $callable, string $label, bool $is_notable, Objective ...$preconditions) {
		$this->callable = $callable;
		$this->label = $label;
		$this->is_notable = $is_notable;
		$this->preconditions = $preconditions;
	}

	/**
	 * @inheritdocs
	 */
	public function getHash() : string {
		return hash(
			"sha256",
			spl_object_hash($this)
		);
	}

	/**
	 * @inheritdocs
	 */
	public function getLabel() : string {
		return $this->label;
	}

	/**
	 * @inheritdocs
	 */
	public function isNotable() : bool {
		return $this->is_notable;
	}

	/**
	 * @inheritdocs
	 */
	public function getPreconditions(Environment $environment) : array {
		return $this->preconditions;
	}

	/**
	 * @inheritdocs
	 */
	public function achieve(Environment $environment) : Environment {
		$res = call_user_func($this->callable, $environment);
		if ($res instanceof Environment) {
			return $res;
		}
		return $environment;
	}
}
