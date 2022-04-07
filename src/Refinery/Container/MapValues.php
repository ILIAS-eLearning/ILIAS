<?php declare(strict_types=1);

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Container;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use InvalidArgumentException;

/**
 * Adds to any array keys for each value
 */
class MapValues implements Transformation
{
    use DeriveInvokeFromTransform;
    use DeriveApplyToFromTransform;

    protected string $type;
    private Transformation $trafo;
    private Factory $factory;

    public function __construct(Transformation $trafo, Factory $factory)
    {
        $this->trafo = $trafo;
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (!is_array($from)) {
            throw new InvalidArgumentException(__METHOD__ . " argument is not an array.");
        }

        return array_map(function ($a) {
            return $this->trafo->transform($a);
        }, $from);
    }
}
