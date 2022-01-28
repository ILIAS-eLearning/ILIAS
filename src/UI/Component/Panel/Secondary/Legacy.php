<?php declare(strict_types=1);

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Secondary;

use ILIAS\UI\Component;

/**
 * Interface Legacy
 * @package ILIAS\UI\Component\Panel\Secondary
 */
interface Legacy extends Secondary
{
    /**
     * Get item list
     */
    public function getLegacyComponent() : Component\Legacy\Legacy;
}
