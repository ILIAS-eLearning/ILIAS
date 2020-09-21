<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ProblemBuilder;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Data;
use ILIAS\Refinery\ConstraintViolationException;

class ByTrying implements Transformation
{
    use DeriveApplyToFromTransform;
    use ProblemBuilder;

    /**
     * @var Transformation[]
     */
    protected $transformations;

    /**
    * @var Data\Factory
    */
    protected $data_factory;

    public function __construct(array $transformations, Data\Factory $data_factory, \ilLanguage $lng)
    {
        $this->transformations = $transformations;
        $this->data_factory = $data_factory;
        $this->error = function () {
            throw new ConstraintViolationException(
                'no valid constraints',
                'no_valid_constraints'
            );
        };
    }

    /**
     * @inheritdoc
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        foreach ($this->transformations as $transformation) {
            if ($transformation->accepts($from)) {
                $result = $this->data_factory->ok($from);
                return $transformation->applyTo($result)->value();
            }
        }
        throw new \Exception($this->getErrorMessage($from));
    }
}
