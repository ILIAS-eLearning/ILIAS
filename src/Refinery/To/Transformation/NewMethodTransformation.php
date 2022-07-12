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

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use InvalidArgumentException;

class NewMethodTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    private object $object;
    private string $method;

    public function __construct(object $object, string $methodToCall)
    {
        if (false === method_exists($object, $methodToCall)) {
            throw new InvalidArgumentException(
                'The second parameter MUST be an method of the object'
            );
        }

        $this->object = $object;
        $this->method = $methodToCall;
    }

    /**
     * @inheritDoc
     */
    public function transform($from)
    {
        if (false === is_array($from)) {
            $from = [$from];
        }

        return call_user_func_array([$this->object, $this->method], $from);
    }
}
