<?php

/* Copyright (c) 2021 Adrian Lüthi <adi.l@bluewin.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Markup;

/**
 * This is how the factory for UI elements looks. This should provide access
 * to all UI elements at some point.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: Disblays markup content as html in ilias
     * ----
     * @param string $content
     * @return  \ILIAS\UI\Component\Markup\Markup
     */
    public function markup($content);
}
