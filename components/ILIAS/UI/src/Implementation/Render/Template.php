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

namespace ILIAS\UI\Implementation\Render;

/**
 * Interface to templating as it is used in the UI framework.
 * This deliberately is much smaller than ilTemplate, there is a lot of stuff in
 * there we should not be using here.
 */
interface Template
{
    /**
     * Set the block to work on.
     */
    public function setCurrentBlock(string $name): bool;

    /**
     * Parse the block that is currently worked on.
     */
    public function parseCurrentBlock(): bool;

    /**
     * Touch a block without working further on it.
     */
    public function touchBlock(string $name): bool;

    /**
     * Set a variable in the current block.
     * @param mixed $value should be possible to be cast to string.
     */
    public function setVariable(string $name, $value): void;

    /**
     * Get the rendered template or a specific block.
     */
    public function get(string $block = null): string;

    /**
     * Add some javascript to be executed on_load of the rendered page.
     * TODO: This seems to be no rendering, but a javascript concern. We should
     * revise this when introducing patterns for javascript.
     */
    public function addOnLoadCode(string $code): void;
}
