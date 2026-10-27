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

namespace ILIAS\WOPI\Discovery;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface ActionRepository
{
    public function hasEditActionForSuffix(
        string $suffix
    ): bool;

    public function hasViewActionForSuffix(
        string $suffix
    ): bool;

    public function hasActionForSuffix(
        string $suffix,
        ActionTarget ...$action_target
    ): bool;

    public function getActionForSuffix(
        string $suffix,
        ActionTarget $action_target
    ): ?Action;

    public function getEditActionForSuffix(
        string $suffix
    ): ?Action;

    public function getViewActionForSuffix(
        string $suffix
    ): ?Action;

    /**
     * @return Action[]
     */
    public function getActions(): array;

    /**
     * @return Action[]
     */
    public function getActionsForTarget(ActionTarget $action_target): array;
    public function getActionsForTargets(ActionTarget ...$action_target): array;

    public function getSupportedSuffixes(ActionTarget ...$action_target): array;

    /**
     * @return string[] suffixes a new, empty document can be created for, sorted
     */
    public function getCreatableSuffixes(bool $with_templates = false): array;

    /**
     * @return array<string, string> suffix => name of the app creating it, sorted by the name
     */
    public function getCreatableFormats(bool $with_templates = false): array;

    /**
     * @return string[] suffixes of templates such as dotx a new document can be created for, sorted
     */
    public function getTemplateSuffixes(): array;

    public function clearSuperfluous(Action ...$actions): void;

    public function store(Action $action, App $for_app): void;

    public function clear(): void;
}
