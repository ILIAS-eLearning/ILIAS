<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public function standard(array $items): \ILIAS\UI\Component\Dropdown\Standard;
}
