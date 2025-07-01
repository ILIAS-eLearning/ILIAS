<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Component\Input\Field;

/**
 * This describes switchable group inputs.
 */
interface SwitchableGroup extends Group
{
    /**
     * Setting withDisabledGroupSwitch to true, the first-level options will
     * be disabled, but the lower inputs will still be operable.
     * You will still have to set the input's value to select the pinned option.
     *
     * @deprecated with 10.
     */
    public function withDisabledGroupSwitch(bool $flag): self;
}
