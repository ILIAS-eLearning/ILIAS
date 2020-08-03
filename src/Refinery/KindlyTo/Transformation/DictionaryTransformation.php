<?php declare(strict_types=1);

/* Copyright (c) 2020 Luka K. A. Stocker, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ConstraintViolationException;

class DictionaryTransformation implements Transformation
{
    use DeriveApplyToFromTransform;

    private $transformation;

    public function __construct(Transformation $transformation)
    {
        $this->transformation = $transformation;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (!is_array($from)) {
            throw new ConstraintViolationException(
                sprintf('The value "%s" is no array.', $from),
                'value_is_no_array',
                $from
            );
        }

        $result = [];
        foreach ($from as $key => $value) {
            if (!is_string($key)) {
                throw new ConstraintViolationException(
                    'Key is not a string',
                    'key_is_no_string'
                );
            }
            $transformedValue = $this->transformation->transform($value);
            $result[$key] = $transformedValue;
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }
}
