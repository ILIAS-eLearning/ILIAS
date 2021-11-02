<?php declare(strict_types=1);

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Dropdown;

use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Divider\Horizontal;
use ILIAS\UI\Component\Link\Standard;

interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *       The Standard Dropdown is the default Dropdown to be used in ILIAS. If
     *       there is no good reason using another Dropdown instance in ILIAS, this
     *       is the one that should be used.
     *   composition: >
     *       The Standard Dropdown uses the primary color as background.
     * rules:
     *   usage:
     *       1: >
     *          Standard Dropdown MUST be used if there is no good reason using
     *          another instance.
     * ---
     * @param array<Shy|Horizontal|Standard> array of action items
     * @return \ILIAS\UI\Component\Dropdown\Standard
     */
    public function standard(array $items) : \ILIAS\UI\Component\Dropdown\Standard;
}
