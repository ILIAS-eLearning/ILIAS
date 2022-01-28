<?php declare(strict_types=1);

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
    public function setCurrentBlock(string $name) : bool;

    /**
     * Parse the block that is currently worked on.
     */
    public function parseCurrentBlock() : bool;

    /**
     * Touch a block without working further on it.
     */
    public function touchBlock(string $name) : bool;

    /**
     * Set a variable in the current block.
     * @param mixed $value should be possible to be cast to string.
     */
    public function setVariable(string $name, $value) : void;

    /**
     * Get the rendered template or a specific block.
     */
    public function get(string $block = null) : string;

    /**
     * Add some javascript to be executed on_load of the rendered page.
     * TODO: This seems to be no rendering, but a javascript concern. We should
     * revise this when introducing patterns for javascript.
     */
    public function addOnLoadCode(string $code) : void;
}
