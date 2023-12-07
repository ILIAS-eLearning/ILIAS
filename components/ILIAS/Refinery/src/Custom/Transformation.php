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

namespace ILIAS\Refinery\Custom;

use ILIAS\Refinery\Transformable;

/**
 * Transform values according to custom configuration
 */
class Transformation implements Transformable
{
    /** @var callable */
    private $transformation;

    public function __construct(callable $transformation)
    {
        $this->transformation = $transformation;
    }

    public function transform($from)
    {
        return ($this->transformation)($from);
    }
}
