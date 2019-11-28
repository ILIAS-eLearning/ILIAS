<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

use ILIAS\UI;

/**
 * A wrapper around an objective that adds some preconditions.
 *
 * ATTENTION: This will use the same hash then the original objective and will
 * therefore be indistinguishable.
 */
class ObjectiveWithPreconditions implements Objective {
	/**
	 * @var Objective
	 */
	protected $original;

	/**
	 * @var Objective[]
	 */
	protected $preconditions;

	public function __construct(Objective $original, Objective ...$preconditions) {
		if (count($preconditions) === 0) {
			throw new \InvalidArgumentException(
				"Expected at least one precondition."
			);
		}
		$this->original = $original;
		$this->preconditions = $preconditions;
	}

	/**
	 * @inheritdocs
	 */
	public function getHash() : string {
		return $this->original->getHash();
	}

	/**
	 * @inheritdocs
	 */
	public function getLabel() : string {
		return $this->original->getLabel();
	}

	/**
	 * @inheritdocs
	 */
	public function isNotable() : bool {
		return $this->original->isNotable();
	}

	/**
	 * @inheritdocs
	 */
	public function getPreconditions(Environment $environment) : array {
		return array_merge($this->preconditions, $this->original->getPreconditions($environment));
	}

	/**
	 * @inheritdocs
	 */
	public function achieve(Environment $environment) : Environment {
		return $this->original->achieve($environment);
	}
}
