<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function toDictionary()
{
    global $DIC;

    $refinery = $DIC->refinery();

    $transformation = $refinery->to()->dictOf(new \ILIAS\Refinery\To\Transformation\IntegerTransformation());

    $result = $transformation->transform(array('sum' => 5, 'user_id' => 1, 'size' => 4));

    return assert(array('sum' => 5, 'user_id' => 1, 'size' => 4) === $result);
}
