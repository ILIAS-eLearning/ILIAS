<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery;

use ILIAS\Data;
use ilLanguage;

class ByTrying implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;
    use ProblemBuilder;

    /**
     * @var Transformation[]
     */
    protected array $transformations;

    protected Data\Factory $data_factory;

    /**
     * @var callable
     */
    protected $error;

    public function __construct(array $transformations, Data\Factory $data_factory, ilLanguage $lng)
    {
        $this->transformations = $transformations;
        $this->data_factory = $data_factory;
        $this->error = static function () : void {
            throw new ConstraintViolationException(
                'no valid constraints',
                'no_valid_constraints'
            );
        };
    }

    /**
     * @inheritdoc
     */
    protected function getError() : callable
    {
        return $this->error;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        foreach ($this->transformations as $transformation) {
            $result = $this->data_factory->ok($from);
            $result = $transformation->applyTo($result);
            if ($result->isOK()) {
                return $result->value();
            }
        }
        throw new \Exception($this->getErrorMessage($from));
    }
}
