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

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Mode implements C\ViewControl\Mode
{
    use ComponentHelper;

    protected array $labeled_actions;
    protected string $aria_label;
    protected ?string $active = null;

    public function __construct($labelled_actions, string $aria_label)
    {
        $this->labeled_actions = $this->toArray($labelled_actions);
        $this->aria_label = $aria_label;
    }

    public function withActive(string $label): C\ViewControl\Mode
    {
        $clone = clone $this;
        $clone->active = $label;
        return $clone;
    }

    public function getActive(): ?string
    {
        return $this->active;
    }

    public function getLabelledActions(): array
    {
        return $this->labeled_actions;
    }

    public function getAriaLabel(): string
    {
        return $this->aria_label;
    }
}
