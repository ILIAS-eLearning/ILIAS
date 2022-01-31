<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Container;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Container\AddLabels;
use ILIAS\Refinery\Custom\Transformation;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class Group
{
    private Factory $dataFactory;

    public function __construct(Factory $dataFactory)
    {
        $this->dataFactory = $dataFactory;
    }

    /**
     * Adds to any array keys for each value
     */
    public function addLabels(array $labels) : addLabels
    {
        return new AddLabels($labels, $this->dataFactory);
    }

    public function mapValues(\ILIAS\Refinery\Transformation $trafo) : MapValues
    {
        return new MapValues($trafo, $this->dataFactory);
    }
}
