<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component;

/**
 * A component is the most general form of an entity in the UI. Every entity
 * is a component.
 *
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
interface Component
{
    /**
     * Get the canonical name of the component.
     *
     * @return string
     */
    public function getCanonicalName();
}
