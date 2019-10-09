<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

/**
 * Registry for resources required by rendered output like Javascript or CSS.
 *
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
interface ResourceRegistry
{
    /**
     * Add a dependency.
     *
     * @param	$name	string
     * @return	self
     */
    public function register($name);
}
