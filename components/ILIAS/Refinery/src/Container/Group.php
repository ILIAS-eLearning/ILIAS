<?php

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

declare(strict_types=1);

namespace ILIAS\Refinery\Container;

use ILIAS\Refinery\Transformation;

class Group
{
    public function __construct(
        private readonly BuildTransformation $build_transformation
    ) {
    }

    /**
     * Adds to any array keys for each value
     * @param string[]|int[] $labels
     * @return Transformation
     */
    public function addLabels(array $labels): Transformation
    {
        return $this->build_transformation->fromTransformable(new AddLabels($labels));
    }

    /**
     * Returns a transformation which applies the given transformation to
     * the element of the array passed to the transformation
     */
    public function mapValues(Transformation $trafo): Transformation
    {
        return $this->build_transformation->fromTransformable(new MapValues($trafo));
    }
}
