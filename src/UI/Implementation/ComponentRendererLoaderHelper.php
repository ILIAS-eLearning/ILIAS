<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Factory as RootFactory;
use ILIAS\UI\Component\Component;

/**
 * Helpers for loaders of component renderers. 
 */
trait ComponentRendererLoaderHelper {
	/**
	 * Get and collapse the names of the passes components.
	 *
	 * @param	Component[]	$contexts
	 * @return	string[]
	 */
	protected function getContextNames(array $contexts) {
		$names = [];
		foreach ($contexts as $context) {
			$names[] = str_replace(" ", "", $context->getName());
		}
		return $names;
	}
}
