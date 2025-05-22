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

namespace ILIAS\GlobalScreen\Scope\Footer\Factory;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Signal;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait hasActionTrait
{
    protected URI|Signal $action;

    protected bool $open_in_new_viewport = false;

    public function withAction(URI|Signal $action): self
    {
        $clone = clone $this;
        $clone->action = $action;
        return $clone;
    }

    public function getAction(): URI|Signal
    {
        return $this->action;
    }

    public function withOpenInNewViewport(bool $state)
    {
        $clone = clone $this;
        $clone->open_in_new_viewport = $state;
        return $clone;
    }

    public function mustOpenInNewViewport(): bool
    {
        if ($this->action instanceof Signal) {
            return false;
        }
        return $this->open_in_new_viewport;
    }
}
