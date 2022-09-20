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

namespace ILIAS\Refinery\URI;

use ILIAS\Data\URI;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveInvokeFromTransform;

class StringTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @inheritDoc
     */
    public function transform($from): string
    {
        if (false === $from instanceof URI) {
            throw new ConstraintViolationException(
                sprintf('The value MUST be of type "%s"', URI::class),
                'not_uri_object'
            );
        }

        /** @var URI $from */
        $result = $from->getBaseURI();

        $query = $from->getQuery();
        if (null !== $query) {
            $query = '?' . $query;
        }
        $result .= $query;

        $fragment = $from->getFragment();
        if (null !== $fragment) {
            $fragment = '#' . $fragment;
        }
        $result .= $fragment;

        return $result;
    }
}
