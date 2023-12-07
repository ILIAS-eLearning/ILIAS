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

namespace ILIAS\Refinery;

use Exception;

class ByTrying implements Transformable
{
    /**
     * @param Transformable[] $transformations
     */
    public function __construct(private readonly array $transformations)
    {
    }

    public function transform($from)
    {
        foreach ($this->transformations as $transformation) {
            $result = $transformation->applyTo(new Ok($result));
            if ($result->isOK()) {
                return $result->value();
            }
        }
        throw new Rejection($from, 'no valid constraints', 'no_valid_constraints');
    }
}
