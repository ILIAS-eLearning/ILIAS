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

use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\GlobalScreen\Scope\Toast\Collector\Renderer\ToastRenderer;

/**
 * Interface isItem
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface isStandardItem extends isItem
{
    public function getTitle(): string;

    public function withDescription(string $description): isStandardItem;

    public function getDescription(): ?string;

    /**
     * A ToastAction leads into the rendered toast to a link that can be clicked by the user. The user is forwarded to
     * an endpoint of the GlobalScreen, where the corresponding callable is executed. Since this is currently only
     * possible synchronously and not asynchronously, the callable must then forward to a
     * URL with $DIC->ctrl()->redirectToURL('...');
     */
    public function withAdditionToastAction(ToastAction $action): isStandardItem;

    /**
     * @return ToastAction[]
     */
    public function getAllToastActions(): array;

    /**
     * @return ToastAction[]
     */
    public function getAdditionalToastActions(): array;

    public function withIcon(Icon $icon): isStandardItem;

    public function getIcon(): ?Icon;

    /**
     * Set the callable to be executed, when the toast is shown. This callable is executed asynchronously,
     * so it does not need to be redirected.
     */
    public function withShownCallable(\Closure $handle_shown): isStandardItem;

    /**
     * Get the callable to be executed, when the test is shown in GUI.
     */
    public function getShownAction(): ToastAction;

    public function hasShownAction(): bool;

    /**
     * Set the callable to be executed, when this specific item is closed by clicking the X button or after it vanishes.
     * This callable is executed asynchronously, so it does not need to be redirected.
     */
    public function withClosedCallable(\Closure $handle_closed): isStandardItem;

    /**
     * Get the callable to be executed, when this specific item is closed.
     */
    public function getClosedAction(): ?ToastAction;

    /**
     * Get whether there are any callables to be executed the Toast is closed.
     */
    public function hasClosedAction(): bool;

    /**
     * Set the callable to be executed, when this specific item is closed vanishing.
     * This callable is executed asynchronously, so it does not need to be redirected.
     */
    public function withVanishedCallable(\Closure $handle_vanished): isStandardItem;

    /**
     * Get the callable to be executed, when this specific item has vanished.
     */
    public function getVanishedAction(): ?ToastAction;

    /**
     * Get whether there are any callables to be executed the Toast has vanished.
     */
    public function hasVanishedAction(): bool;

    public function withVanishTime(int $miliseconds): isStandardItem;

    public function getVanishTime(): ?int;

    public function withDelayTime(int $miliseconds): isStandardItem;

    public function getDelayTime(): ?int;

    public function getRenderer(): ToastRenderer;
}
