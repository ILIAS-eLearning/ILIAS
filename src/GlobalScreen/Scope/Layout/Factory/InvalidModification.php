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

namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class InvalidModification extends \InvalidArgumentException
{
    public function __construct(LayoutModification $modification, $message = "")
    {
        // Context
        $modification_class = get_class($modification);
        $closure_file = 'Unknown';
        $closure_line = 0;
        try {
            $closure = $modification->getModification();
            $reflection = new \ReflectionFunction($closure);
            $closure_file = $reflection->getClosureScopeClass()->getName();
            $closure_line = $reflection->getStartLine();
        } catch (\Throwable $e) {
            // ignore
        }
        $message = sprintf(
            "Invalid modification %s in %s (Line %s). %s",
            $modification_class,
            $closure_file,
            $closure_line,
            $message
        );

        parent::__construct($message, 0);
    }
}
