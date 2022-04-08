<?php declare(strict_types=1);

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

    public function __construct(Transformation $trafo)
    {
        $this->trafo = $trafo;
    }

    /**
     * @inheritDoc
     */
    public function transform($from) : array
    {
        if (!is_array($from)) {
            throw new InvalidArgumentException(__METHOD__ . " argument is not an array.");
        }

        return array_map(function ($a) {
            return $this->trafo->transform($a);
        }, $from);
    }
}
