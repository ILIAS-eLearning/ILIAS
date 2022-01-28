<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;

/**
 * Helpers for loaders of component renderers.
 */
trait LoaderHelper
{
    /**
     * Get and collapse the names of the passes components.
     *
     * @param	Component[]	$contexts
     * @return	string[]
     */
    protected function getContextNames(array $contexts) : array
    {
        $names = [];
        foreach ($contexts as $context) {
            $names[] = str_replace(" ", "", $context->getCanonicalName());
        }
        return $names;
    }
}
