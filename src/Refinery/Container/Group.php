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

namespace ILIAS\Refinery\Container;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Transformation;

class Group
{
    private Factory $dataFactory;

    public function __construct(Factory $dataFactory)
    {
        $this->dataFactory = $dataFactory;
    }

    /**
     * Adds to any array keys for each value
     * @param string[]|int[] $labels
     * @return Transformation
     */
    public function addLabels(array $labels): Transformation
    {
        return new AddLabels($labels, $this->dataFactory);
    }

    /**
     * Returns a transformation which applies the given transformation to
     * the element of the array passed to the transformation
     */
    public function mapValues(Transformation $trafo): Transformation
    {
        return new MapValues($trafo);
    }
}
