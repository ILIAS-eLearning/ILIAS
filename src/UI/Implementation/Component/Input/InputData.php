<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

use LogicException;

/**
 * Describes how Input-Elements want to interact with posted data.
 * This basically is a glorified array.
 */
interface InputData
{
    /**
     * Get a named value from the data.
     *
     * @throws    LogicException    if name is not in data
     * @return    mixed
     */
    public function get(string $name);

    /**
     * Get a named value from the data and fallback to default
     * if that name does not exist.
     *
     * @param    mixed  $default
     * @return    mixed
     */
    public function getOr(string $name, $default);
}
