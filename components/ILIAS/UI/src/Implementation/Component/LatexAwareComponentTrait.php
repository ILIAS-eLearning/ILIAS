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

namespace ILIAS\UI\Implementation\Component;

use ILIAS\UI\Component\LatexAwareComponent;

trait LatexAwareComponentTrait
{
    protected ?bool $latex_enabled = null;

    /**
     * @see LatexAwareComponent::withLatexEnabled()
     */
    public function withLatexEnabled(): static
    {
        $clone = clone($this);
        $clone->latex_enabled = true;
        return $clone;
    }

    /**
     * @see LatexAwareComponent::withLatexDisabled()
     */
    public function withLatexDisabled(): static
    {
        $clone = clone($this);
        $clone->latex_enabled = false;
        return $clone;
    }

    /**
     * @see LatexAwareComponent::isLatexEnabled()
     */
    public function isLatexEnabled(): bool
    {
        return $this->latex_enabled === true;
    }

    /**
     * @see LatexAwareComponent::isLatexDisabled()
     */
    public function isLatexDisabled(): bool
    {
        return $this->latex_enabled === false;
    }
}
