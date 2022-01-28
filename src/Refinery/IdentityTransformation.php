<?php declare(strict_types=1);

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Refinery;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\DeriveApplyToFromTransform;

class IdentityTransformation implements Transformation
{
    use DeriveInvokeFromTransform;
    use DeriveApplyToFromTransform;

    /**
     * @throws \InvalidArgumentException
     */
    public function transform($from)
    {
        return $from;
    }
}
