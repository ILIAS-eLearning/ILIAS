<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
namespace ILIAS\DIC;
/**
 * Customizing of pimple-DIC for ILIAS.
 *
 * This just exposes some of the services in the container as plain methods
 * to help IDEs when using ILIAS.
 *
 * TODO:
 *  - Could be a good idea to encode the names of the services as constants
 *    instead of using strings as names.
 */
class Container extends \Pimple\Container {
	/**
	 * @return	ilDB
	 */
	public function ilDB() {
		return $this["ilDB"];
	}
}