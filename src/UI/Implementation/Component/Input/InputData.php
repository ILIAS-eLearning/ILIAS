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
