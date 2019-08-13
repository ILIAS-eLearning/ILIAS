<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\URI;

use ILIAS\Data\URI;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;

class StringTransformation  implements Transformation
{
    use DeriveApplyToFromTransform;

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (false === $from instanceof URI) {
            throw new ConstraintViolationException(
                sprintf('The value MUST be of type "%s"', URI::class),
                'not_uri_object'
            );
        }

        /** @var URI $from */
        $result = $from->getBaseURI();

        $query  = $from->getQuery();
        if (null !== $query) {
            $query = '?' . $query;
        }
        $result .= $query;

        $fragment = $from->getFragment();
        if (null !== $fragment) {
            $fragment = '#' . $fragment;
        }
        $result   .= $fragment;

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }
}
