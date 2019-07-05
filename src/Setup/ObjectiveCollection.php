<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

use ILIAS\UI;

/**
 * A objective collection is a objective that is achieved once all subobjectives are achieved.
 */
class ObjectiveCollection implements Objective {
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
	protected $objectives;

	public function __construct(string $label, bool $is_notable, Objective ...$objectives) {
		$this->label = $label;
		$this->is_notable = $is_notable;
		$this->objectives = $objectives;
	}

	/**
	 * @return Objective[]
	 */
	public function getObjectives() : array {
		return $this->objectives;
	}

	/**
	 * @inheritdocs
	 */
	public function getHash() : string {
		return hash(
			"sha256",
			implode(
				array_map(
					function($g) { return $g->getHash(); },
					$this->objectives
				)
			)
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
		return $this->objectives;
	}

	/**
	 * @inheritdocs
	 */
	public function achieve(Environment $environment) : Environment {
		return $environment;
	}
}
