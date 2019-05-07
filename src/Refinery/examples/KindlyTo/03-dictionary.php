<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function toDictionary() {
	global $DIC;

	$refinery = $DIC->refinery();

	$transformation = $refinery->kindlyTo()->dictOf(new \ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation());

	$result = $transformation->transform(array('sum' => '5', 'user_id' => 1, 'size' => 4.3));

	return assert(array('sum' => 5, 'user_id' => 1, 'size' => 4) === $result);
}
