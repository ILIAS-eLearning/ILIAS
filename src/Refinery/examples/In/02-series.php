<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function series()
{
    global $DIC;

    $refinery = $DIC->refinery();

    $transformation = $refinery->in()->series(
        array(
            new ILIAS\Refinery\To\Transformation\IntegerTransformation(),
            new ILIAS\Refinery\To\Transformation\IntegerTransformation(),
        )
    );

    $result = $transformation->transform(5);

    return assert(5 === $result);
}
