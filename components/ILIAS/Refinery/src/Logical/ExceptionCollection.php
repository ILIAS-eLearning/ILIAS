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

namespace ILIAS\Refinery\Logical;

use Exception;

class ExceptionCollection extends Exception
{
    /**
     * @param list<Exception> $exceptions
     */
    public function __construct(private readonly array $exceptions)
    {
        parent::__construct(join("\n", array_map(fn(Exception $e) => $e->getMessage(), $this->exceptions)));
    }

    public function exceptions(): array
    {
        return $this->exceptions;
    }
}
