<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function toTuple() {
	global $DIC;

	$refinery = $DIC->refinery();

	$transformation = $refinery->kindlyTo()->tupleOf(
		array(
			new \ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation(),
			new \ILIAS\Refinery\KindlyTo\Transformation\StringTransformation()
		)
	);

	$result = $transformation->transform(array(5.3, 2));

	return assert(array(5, '2') === $result);
}
