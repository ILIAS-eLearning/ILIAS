<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

/**
 * Describes how Input-Elements want to interact with posted data.
 *
 * This basically is a glorified array.
 */
interface PostData
{

    /**
     * Get a named value from the data.
     *
     * @param    string $name
     *
     * @throws    \LogicException    if name is not in data
     * @return    mixed
     */
    public function get($name);


    /**
     * Get a named value from the data and fallback to default
     * if that name does not exist.
     *
     * @param    string $name
     * @param    mixed  $default
     *
     * @return    mixed
     */
    public function getOr($name, $default);
}
