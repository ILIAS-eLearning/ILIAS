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

    /** @var callable */
    private $transformation;

    public function __construct(callable $transformation)
    {
        $this->transformation = $transformation;
    }

    /**
     * @inheritDoc
     */
    public function transform($from)
    {
        return call_user_func($this->transformation, $from);
    }
}
