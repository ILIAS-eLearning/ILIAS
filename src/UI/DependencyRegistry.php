<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI;

/**
 * Registry for dependencies of rendered output like Javascript or CSS.
 *
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
interface DependencyRegistry {
	/**
	 * Add a dependency.
	 *
	 * @param	$name	string
	 * @return	self
	 */
	public function register($name);

	/**
	 * Get dependencies.
	 *
	 * Every dependency should only appear once.
	 *
	 * @return	string[]
	 */
	public function get();
}
