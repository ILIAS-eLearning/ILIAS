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

namespace ILIAS\GlobalScreen\Scope;

use Closure;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait VisibilityAvailabilityTrait
{
    protected ?Closure $available_callable = null;
    protected ?Closure $visiblility_callable = null;

    private ?bool $is_visible_static = null;

    public function withVisibilityCallable(callable $is_visible): self
    {
        $clone = clone($this);
        $clone->visiblility_callable = $is_visible;
        $clone->is_visible_static = null;

        return $clone;
    }

    public function isVisible(): bool
    {
        if (isset($this->is_visible_static)) {
            return $this->is_visible_static;
        }
        if (!$this->isAvailable()) {
            return $this->is_visible_static = false;
        }
        if (is_callable($this->visiblility_callable)) {
            $callable = $this->visiblility_callable;

            $value = (bool) $callable();

            return $this->is_visible_static = $value;
        }

        return $this->is_visible_static = true;
    }

    public function withAvailableCallable(callable $is_available): self
    {
        $clone = clone($this);
        $clone->available_callable = $is_available;

        return $clone;
    }

    public function isAvailable(): bool
    {
        if ($this->isAlwaysAvailable() === true) {
            return true;
        }
        if (is_callable($this->available_callable)) {
            $callable = $this->available_callable;

            return (bool) $callable();
        }

        return true;
    }

    public function isAlwaysAvailable(): bool
    {
        return false;
    }
}
