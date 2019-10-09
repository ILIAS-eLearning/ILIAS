<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

/**
 * Interface to templating as it is used in the UI framework.
 *
 * This deliberately is much smaller than ilTemplate, there is a lot of stuff in
 * there we should not be using here.
 */
interface Template
{
    /**
     * Set the block to work on.
     *
     * @param	string	$name
     * @return	null
     */
    public function setCurrentBlock($name);

    /**
     * Parse the block that is currently worked on.
     *
     * @return 	null
     */
    public function parseCurrentBlock();

    /**
     * Touch a block without working further on it.
     *
     * @param	string	$name
     * @return 	null
     */
    public function touchBlock($name);

    /**
     * Set a variable in the current block.
     *
     * @param	string	$name
     * @param	mixed	$value	should be coercible to string
     * @return 	null
     */
    public function setVariable($name, $value);

    /**
     * Get the rendered template or a specific block.
     *
     * @param	string|null		$block
     * @return	string
     */
    public function get($block = null);

    /**
     * Add some javascript to be executed on_load of the rendered page.
     *
     * TODO: This seems to be no rendering, but a javascript concern. We should
     * revise this when introducing patterns for javascript.
     */
    public function addOnLoadCode($code);
}
