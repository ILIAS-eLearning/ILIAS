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

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;

class ListTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    private Transformation $transformation;

    public function __construct(Transformation $transformation)
    {
        $this->transformation = $transformation;
    }

    /**
     * @inheritDoc
     */
    public function transform($from): array
    {
        if (!is_array($from)) {
            $from = [$from];
        }

        $result = [];
        foreach ($from as $val) {
            $transformedVal = $this->transformation->transform($val);
            $result[] = $transformedVal;
        }

        return $result;
    }
}
