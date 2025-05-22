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

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component\Button as B;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\NotImplementedException;

class Factory implements B\Factory
{
    public function standard(string $label, $action): Standard
    {
        return new Standard($label, $action);
    }

    public function primary(string $label, $action): Primary
    {
        return new Primary($label, $action);
    }

    public function close(): Close
    {
        return new Close();
    }

    public function minimize(): Minimize
    {
        return new Minimize();
    }

    public function tag(string $label, $action): Tag
    {
        return new Tag($label, $action);
    }

    public function shy(string $label, $action): Shy
    {
        return new Shy($label, $action);
    }

    public function month(string $default): Month
    {
        return new Month($default);
    }

    public function bulky(Symbol $symbol, string $label, string $action): Bulky
    {
        return (new Bulky($label, $action))->withSymbol($symbol);
    }

    public function toggle(
        string $label,
        $on_action,
        $off_action,
        bool $is_on = false,
        ?Signal $click_signal = null
    ): Toggle {
        return new Toggle($label, $on_action, $off_action, $is_on, $click_signal);
    }
}
