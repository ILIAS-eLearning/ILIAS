<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Secondary;

/**
 * Interface Legacy
 * @package ILIAS\UI\Component\Panel\Secondary
 */
interface Legacy extends Secondary
{

    /**
     * Get item list
     *
     * @return \ILIAS\UI\Component\Legacy\Legacy
     */
    public function getLegacyComponent() : \ILIAS\UI\Component\Legacy\Legacy;
}
