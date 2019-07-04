<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * Some facility to load configurations.
 */
interface ConfigurationLoader {
	public function loadConfigurationFor(string $type) : Config;
}
