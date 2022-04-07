<?php declare(strict_types=1);

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Custom;

use ILIAS\Data\Factory;
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
    private Factory $factory;

    /**
     * @param callable $transform
     * @param Factory $factory
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
