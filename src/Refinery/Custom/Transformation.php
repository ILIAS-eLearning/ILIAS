<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Custom;

use ILIAS\Data\Factory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation as TransformationInterface;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;

/**
 * Transform values according to custom configuration
 */
class Transformation implements TransformationInterface
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @var callable
     */
    protected $transform;
    private $factory;

    /**
     * @param callable $transform
     * @param Factory|null $factory
     */
    public function __construct(callable $transform, Factory $factory)
    {
        $this->transform = $transform;
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        return call_user_func($this->transform, $from);
    }
}
