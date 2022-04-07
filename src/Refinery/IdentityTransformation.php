<?php declare(strict_types=1);

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Refinery;

use InvalidArgumentException;

class IdentityTransformation implements Transformation
{
    use DeriveInvokeFromTransform;
    use DeriveApplyToFromTransform;

    /**
     * @throws InvalidArgumentException
     */
    public function transform($from)
    {
        return $from;
    }
}
