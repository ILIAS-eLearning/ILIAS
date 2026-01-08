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

namespace ILIAS\UI\Implementation\Component\Input;

/**
 * Generates input names for both HTML rendering and input data processing.
 *
 * A NameSource acts as a stateful builder for hierarchical input names. Its
 * state must be reset with withReset() before starting a new naming process.
 * Each Input retrieves a name via getNextName(). For nested Inputs, the previous
 * name SHOULD be passed to withParentName(), creating a path-like hierarchy
 * where parent and child names are concatenated using some delimiter.
 */
interface NameSource
{
    /**
     * Get the next input name for the current naming process.
     *
     * A dedicated name will take precedence over a generated one.
     *
     * @throws \LogicException if there are duplicate names on one level.
     */
    public function getNextName(?string $dedicated_name = null): string;

    /**
     * Get a NameSource like this, but provide it with a parent name.
     *
     * The parent name will be concatenated before the next input name and is
     * separated by some delimiter.
     */
    public function withParentName(string $parent_name): static;

    /**
     * Get a NameSource like this, but reset its internal state.
     *
     * Use this method before starting a new naming process to ensure
     * previous state does not affect new name generation.
     */
    public function withReset(): static;
}
