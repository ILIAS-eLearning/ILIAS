<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Transformation\Transformations;

use ILIAS\Transformation\Transformation;

/**
 * Transform values according to custom configuration
 */
class Custom implements Transformation
{
    /**
     * @var callable
     */
    protected $transform;

    /**
     * @param string 	$delimiter
     */
    public function __construct(callable $transform)
    {
        $this->transform = $transform;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        return call_user_func($this->transform, $from);
    }

    /**
     * @inheritdoc
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }
}
