<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Renderer;

/**
 * Base class for all component renderers.
 */
abstract class AbstractRenderer implements Renderer {
	/**
 	 * Component renderers must not depend on anything.
	 */
	final public function __construct() {
	}
}
