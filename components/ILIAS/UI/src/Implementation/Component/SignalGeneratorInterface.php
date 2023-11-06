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

namespace ILIAS\UI\Implementation\Component;

/**
 * Interface SignalGeneratorInterface
 *
 * @package ILIAS\UI\Component
 */
interface SignalGeneratorInterface
{
    /**
     * Create a signal, each created signal MUST have a unique ID.
     *
     * @param string $class Fully qualified class name (including namespace) of desired signal subtype
     */
    public function create(string $class = ''): Signal;
}
