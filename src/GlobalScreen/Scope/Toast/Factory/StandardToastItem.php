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

namespace ILIAS\GlobalScreen\Scope\Toast\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\GlobalScreen\Scope\Toast\Collector\Renderer\ToastRenderer;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class StandardToastItem implements isStandardItem
{
    private const ACTION_SHOWN = 'shown';
    private const ACTION_CLOSED = 'closed';
    private const ACTION_VANISHED = 'vanished';

    protected string $title;
    protected ?string $description = null;
    protected ?Icon $icon = null;
    protected array $additional_actions = [];

    /**
     * Callable to be executed, if the notification center has been opened.
     */
    protected ?ToastAction $handle_shown = null;

    /**
     * Callable to be executed, if this specific item has been closed.
     */
    protected ?ToastAction $handle_closed = null;
    /**
     * Callable to be executed, if this specific item has vanished.
     */
    private ?ToastAction $handle_vanished = null;
    protected IdentificationInterface $provider_identification;
    protected ToastRenderer $renderer;

    protected ?int $vanish_time = null;
    protected ?int $delay_time = null;

    public function __construct(
        IdentificationInterface $provider_identification,
        ToastRenderer $renderer,
        string $title,
        ?Icon $icon = null
    ) {
        $this->provider_identification = $provider_identification;
        $this->renderer = $renderer;
        $this->title = $title;
        $this->icon = $icon;
    }

    private function packClosure(?\Closure $closure, string $identifier, string $title): ?ToastAction
    {
        if ($closure === null) {
            return null;
        }
        return new ToastAction(
            $identifier,
            $title,
            $closure
        );
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function withDescription(string $description): isStandardItem
    {
        $clone = clone $this;
        $clone->description = $description;
        return $clone;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    final public function withAdditionToastAction(ToastAction $action): isStandardItem
    {
        if (in_array(
            $action->getIdentifier(),
            [self::ACTION_SHOWN, self::ACTION_CLOSED, self::ACTION_VANISHED],
            true
        )) {
            throw new \InvalidArgumentException(
                'You cannot use the reserved identifiers shown, closed or vanished for additional actions'
            );
        }

        $existing = array_map(function (ToastAction $action): string {
            return $action->getIdentifier();
        }, $this->additional_actions);

        if (in_array($action->getIdentifier(), $existing, true)) {
            throw new \InvalidArgumentException(
                'You cannot use the same identifier twice'
            );
        }

        $clone = clone $this;
        $clone->additional_actions[] = $action;
        return $clone;
    }

    final public function getAllToastActions(): array
    {
        $actions = $this->additional_actions;
        $actions[] = $this->handle_shown;
        $actions[] = $this->handle_closed;
        $actions[] = $this->handle_vanished;

        $actions = array_filter($actions, function (?ToastAction $action): bool {
            return $action !== null;
        });

        return $actions;
    }

    final public function getAdditionalToastActions(): array
    {
        return $this->additional_actions;
    }

    public function withIcon(Icon $icon): isStandardItem
    {
        $clone = clone $this;
        $clone->icon = $icon;
        return $clone;
    }

    public function getIcon(): ?Icon
    {
        return $this->icon;
    }

    public function getProviderIdentification(): IdentificationInterface
    {
        return $this->provider_identification;
    }

    final public function withShownCallable(\Closure $handle_shown): isStandardItem
    {
        $clone = clone $this;
        $clone->handle_shown = $this->packClosure(
            $handle_shown,
            self::ACTION_SHOWN,
            self::ACTION_SHOWN
        );

        return $clone;
    }

    final public function getShownAction(): ToastAction
    {
        return $this->handle_shown;
    }

    final public function hasShownAction(): bool
    {
        return $this->handle_shown !== null;
    }

    final public function withClosedCallable(\Closure $handle_closed): isStandardItem
    {
        $clone = clone $this;
        $clone->handle_closed = $this->packClosure(
            $handle_closed,
            self::ACTION_CLOSED,
            self::ACTION_CLOSED
        );

        return $clone;
    }

    final public function getClosedAction(): ?ToastAction
    {
        return $this->handle_closed;
    }

    final public function hasClosedAction(): bool
    {
        return $this->handle_closed !== null;
    }

    public function withVanishedCallable(\Closure $handle_vanished): isStandardItem
    {
        $clone = clone $this;
        $clone->handle_vanished = $this->packClosure(
            $handle_vanished,
            self::ACTION_VANISHED,
            self::ACTION_VANISHED
        );

        return $clone;
    }

    public function getVanishedAction(): ?ToastAction
    {
        return $this->handle_vanished;
    }

    public function hasVanishedAction(): bool
    {
        return $this->handle_vanished !== null;
    }

    public function withVanishTime(int $miliseconds): isStandardItem
    {
        $clone = clone $this;
        $clone->vanish_time = $miliseconds;
        return $clone;
    }

    public function getVanishTime(): ?int
    {
        return $this->vanish_time;
    }

    public function withDelayTime(int $miliseconds): isStandardItem
    {
        $clone = clone $this;
        $clone->delay_time = $miliseconds;
        return $clone;
    }

    public function getDelayTime(): ?int
    {
        return $this->delay_time;
    }

    final public function getRenderer(): ToastRenderer
    {
        return $this->renderer;
    }
}
