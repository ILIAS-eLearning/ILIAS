<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;
use ILIAS\Setup\ConfigurationLoader;
use ILIAS\Setup\UnachievableException;

/**
 * Tries to enumerate all preconditions for the given objective, where the ones that
 * can be achieved (i.e. have no further preconditions on their own) will be
 * returned first. Will also attempt to only return every objective once. This thus 
 * expects, that returned objectives will be achieved somehow.
 */
class ObjectiveIterator implements \Iterator {
	/**
	 * @var	Environment
	 */
	protected $environment;

	/**
	 * @var Objective
	 */
	protected $objective;

	/**
	 * @var Objective[]
	 */
	protected $stack;

	/**
	 * @var Objective|null
	 */
	protected $current;

	/**
	 * @var array<string, bool>
	 */
	protected $returned;

	/**
	 * @var array<string, string[]>
	 */
	protected $reverse_dependencies;


	public function __construct(Environment $environment, Objective $objective) {
		$this->environment = $environment;
		$this->objective = $objective;
		$this->rewind();
	}

	/**
	 * @return void
	 */
	public function setEnvironment(Environment $environment) {
		$this->environment = $environment;
	}

	public function rewind() {
		$this->stack = [$this->objective];
		$this->current = null; 
		$this->returned = [];
		$this->reverse_dependencies = [];
		$this->next();
	}

	public function current() {
		if ($this->current === null) {
			throw new \LogicException(
				"Iterator is finished or wasn't initialized correctly internally."
			);
		}
		return $this->current;
	}

	public function key() {
		return $this->current()->getHash();
	}

	public function next() {
		if (count($this->stack) === 0) {
			$this->current = null;
			return;
		}

		$cur = array_pop($this->stack);
		$hash = $cur->getHash();
		$preconditions = array_filter(
			$cur->getPreconditions($this->environment),
			function ($p) {
				return !isset($this->returned[$p->getHash()]);
			}
		);

		if (count($preconditions) === 0) {
			$this->returned[$hash] = true;
			$this->current = $cur;
			return;
		}

		$this->stack[] = $cur;
		$this->detectDependencyCycles($hash, $hash);
		foreach (array_reverse($preconditions) as $p) {
			$this->stack[] = $p;
			$this->setReverseDependency($p->getHash(), $hash);
		}
		$this->next();
	}

	public function valid() {
		return $this->current !== null;
	}

	protected function detectDependencyCycles(string $cur, string $next) {
		if (!isset($this->reverse_dependencies[$next])) {
			return;
		}
		if (in_array($cur, $this->reverse_dependencies[$next])) {
			throw new UnachievableException(
				"The objectives contain a dependency cycle and won't all be achievable."
			);
		}
		foreach ($this->reverse_dependencies[$next] as $d) {
			$this->detectDependencyCycles($cur, $d);
		}
	}

	protected function setReverseDependency(string $other, string $cur) {
		if (!isset($this->reverse_dependencies[$other])) {
			$this->reverse_dependencies[$other] = [];
		}
		$this->reverse_dependencies[$other][] = $cur;
	}
}
