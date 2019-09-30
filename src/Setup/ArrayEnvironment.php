<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

use ILIAS\Setup;

class ArrayEnvironment implements Environment {
	/**
	 * @var	array<string,mixed>
	 */
	protected $resources;

	 /**
	  * @var array<string,mixed>
	  */

	protected $configs;

	public function __construct(array $resources)
	{
		$this->resources = $resources;
	}

	/**
	 * @inheritdoc
	 */
	public function getResource(string $id)
	{
		if (!isset($this->resources[$id])) {
			return null;
		}
		return $this->resources[$id];
	}

	/**
	 * @inheritdoc
	 */
	public function withResource(string $id, $resource): Environment
	{
		if (isset($this->resources[$id])) {
			throw new \RuntimeException(
				"Resource '$id' is already contained in the environment"
			);
		}
		$clone = new ArrayEnvironment($this->resources);
		$clone->resources[$id] = $resource;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withConfigFor(string $id, $config): Environment
	{
		if (isset($this->configs[$id])) {
			throw new \RuntimeException(
				"Config '$id' is already contained in the environment"
			);
		}
		$clone = new ArrayEnvironment($this->resources);
		$clone->configs[$id] = $config;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getConfigFor(string $id)
	{
		if (!isset($this->configs[$id])) {
			throw new \RuntimeException(
				"Config '$id' is not contained in the environment"
			);
		}
		return $this->configs[$id];
	}
}
