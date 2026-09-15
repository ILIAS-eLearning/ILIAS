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
use Closure;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ReflectionFunction;
use InvalidArgumentException;

class Mode implements C\ViewControl\Mode
{
    use ComponentHelper;

    protected array $labeled_actions;
    protected string $aria_label;
    protected ?string $active = null;
    /** @var Closure[] */
    private array $on_load_code_binder_by_label = [];

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

    public function withOnLoadCodeForAction(string $label, Closure $binder): static
    {
        $this->checkLabelExists($label);
        $this->checkBinder($binder);
        $clone = clone $this;
        $clone->on_load_code_binder_by_label[$label] = $binder;
        return $clone;
    }

    public function withAdditionalOnLoadCodeForAction(string $label, Closure $binder): static
    {
        $current_binder = $this->getOnLoadCodeForAction($label);
        if ($current_binder === null) {
            return $this->withOnLoadCodeForAction($label, $binder);
        }

        $this->checkLabelExists($label);
        $this->checkBinder($binder);
        return $this->withOnLoadCodeForAction($label, static fn($id) => $binder($id) . "\n" . $current_binder($id));
    }

    public function getOnLoadCodeForAction(string $label): ?Closure
    {
        return $this->on_load_code_binder_by_label[$label] ?? null;
    }

    /**
     * @throws \InvalidArgumentException if closure does not take one argument
     */
    private function checkBinder(Closure $binder): void
    {
        $refl = new ReflectionFunction($binder);
        $args = array_map(static fn($arg) => $arg->name, $refl->getParameters());
        if (array("id") !== $args) {
            throw new InvalidArgumentException('Expected closure "$binder" to have exactly one argument "$id".');
        }
    }

    /**
     * @throws InvalidArgumentException if the label does not exist
     */
    private function checkLabelExists(string $label): void
    {
        if (!array_key_exists($label, $this->labeled_actions)) {
            throw new InvalidArgumentException("Label '$label' does not exist in Mode control.");
        }
    }

    public function withOnLoadCodeForActions(array $label_binder_map): static
    {
        $clone = clone $this;
        foreach ($label_binder_map as $label => $binder) {
            $clone = $clone->withOnLoadCodeForAction($label, $binder);
        }
        return $clone;
    }

    public function withAdditionalOnLoadCodeForActions(array $label_binder_map): static
    {
        $clone = clone $this;
        foreach ($label_binder_map as $label => $binder) {
            $clone = $clone->withAdditionalOnLoadCodeForAction($label, $binder);
        }
        return $clone;
    }
}
