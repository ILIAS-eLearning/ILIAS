<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

/**
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
abstract class Renderer implements \ILIAS\UI\Renderer {
	/**
	 * @var	\ILIAS\UI\DependencyRegistry
	 */
	protected $dependency_registry;

	public function __construct(DependencyRegistry $dependency_registry) {
		$this->dependency_registry = $dependency_registry;
	}
}