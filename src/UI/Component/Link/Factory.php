<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Link;

/**
 * Link factory
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *       A standard link is a link with a text label as content of the link.
     *   composition: >
     *       The standard link uses the default link color as text color an no
     *       background.
     *
     * rules:
     *   usage:
     *       1: >
     *          Standard links MUST be used if there is no good reason using
     *          another instance.
     *       2: >
     *          Links to ILIAS screens that contain the general ILIAS
     *          navigation MUST NOT be opened in a new viewport.
     * ---
     * @param	string		$label
     * @param	string		$action
     * @return  \ILIAS\UI\Component\Link\Standard
     */
    public function standard($label, $action);
}
