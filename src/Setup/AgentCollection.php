<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Input as Input;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

/**
 * An agent that is just a collection of some other agents.
 */
class AgentCollection implements Agent {
	/**
	 * @var FieldFactory
	 */
	protected $field_factory;

	/**
	 * @var Refinery
	 */
	protected $refinery;

	/**
	 * @var Agent[]
	 */
	protected $agents;

	public function __construct(
		FieldFactory $field_factory,
		Refinery $refinery,
		array $agents
	) {
		$this->field_factory = $field_factory;
		$this->refinery = $refinery;
		$this->agents = $agents;
	}

	/**
	 * @inheritdocs
	 */
	public function hasConfig() : bool {
		foreach ($this->agents as $c) {
			if ($c->hasConfig()) {
				return true;
			}
		}
		return false;
	}	

	/**
	 * @inheritdocs
	 */
	public function getConfigInput(Config $config = null) : Input {
		if ($config !== null) {
			$this->checkConfig($config);
		}

		$inputs = [];
		foreach ($this->getAgentsWithConfig() as $k => $c) {
			if ($config) {
				$inputs[$k] = $c->getConfigInput($config->getConfig($k));
			}
			else {
				$inputs[$k] = $c->getConfigInput();
			}	
		}

		return $this->field_factory->group($inputs)
			->withAdditionalTransformation(
				$this->refinery->in()->series([
					$this->refinery->custom()->transformation(function($v) {
						return [$v];
					}),
					$this->refinery->to()->toNew(ConfigCollection::class)
				])
			);
	}

	/**
	 * @inheritdocs
	 */
	public function getArrayToConfigTransformation() : Transformation {
		return $this->refinery->in()->series([
			$this->refinery->to()->recordOf(array_map(
				function($a) {
					return $a->getArrayToConfigTransformation();
				},
				array_filter(
					$this->agents,
					function($a) {
						return $a->hasConfig();
					}
				)
			)),
			$this->refinery->custom()->transformation(function($v) {
				return [$v];
			}),
			$this->refinery->to()->toNew(ConfigCollection::class)
		]);
	}

	/**
	 * @inheritdocs
	 */
	public function getInstallObjective(Config $config = null) : Objective {
		return $this->getXObjective("getInstallObjective", $config);
	}

	/**
	 * @inheritdocs
	 */
	public function getUpdateObjective(Config $config = null) : Objective {
		return $this->getXObjective("getUpdateObjective", $config);
	}

	/**
	 * @inheritdocs
	 */
	public function getBuildArtifactObjective(Config $config = null) : Objective {
		return $this->getXObjective("getBuildArtifactObjective", $config);
	}

	protected function getXObjective(string $which, Config $config = null) : Objective {
		$this->checkConfig($config);

		$gs = [];
		foreach ($this->agents as $k => $c) {
			if ($c->hasConfig()) {
				$gs[] = call_user_func([$c, $which], $config->getConfig($k));
			}
			else {
				$gs[] = call_user_func([$c, $which]);
			}
		}

		return new ObjectiveCollection("Collected Update Objectives", false, ...$gs);
	}

	protected function checkConfig(Config $config) {
		if (!($config instanceof ConfigCollection)) {
			throw new \InvalidArgumentException(
				"Expected ConfigCollection for configuration."
			);
		}
	}

	protected function getAgentsWithConfig() : \Traversable {
		foreach ($this->agents as $k => $c) {
			if ($c->hasConfig()) {
				yield $k => $c;
			}
		}
	}
}
