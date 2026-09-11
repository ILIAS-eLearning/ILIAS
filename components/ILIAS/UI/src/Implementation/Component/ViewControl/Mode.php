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

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use InvalidArgumentException;

class Mode implements C\ViewControl\Mode
{
    use ComponentHelper;

    protected array $labeled_actions;
    protected string $aria_label;
    protected ?string $active = null;
    /** @var bool[] */
    protected array $disabled_actions = [];

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

    public function isActionDisabled(string $label): bool
    {
        return $this->disabled_actions[$label] ?? false;
    }

    public function withDisableAllActions(bool $disabled = true): C\ViewControl\Mode
    {
        return $this->withDisableActions(array_fill_keys(array_keys($this->labeled_actions), $disabled));
    }

    public function withDisableAction(string $label, bool $disabled = true): C\ViewControl\Mode
    {
        $clone = clone $this;
        $this->checkLabelExists($label);
        $clone->disabled_actions[$label] = $disabled;
        return $clone;
    }

    public function withDisableActions(array $label_disable_map): C\ViewControl\Mode
    {
        $clone = clone $this;
        foreach ($label_disable_map as $label => $disabled) {
            $clone = $clone->withDisableAction($label, $disabled);
        }
        return $clone;
    }

    private function checkLabelExists(string $label): void
    {
        if (!array_key_exists($label, $this->labeled_actions)) {
            throw new InvalidArgumentException("Label '$label' does not exist in Mode control.");
        }
    }
}
