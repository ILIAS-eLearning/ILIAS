<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function parallel() {
	global $DIC;

	$refinery = $DIC->refinery();

	$transformation = $refinery->in()->parallel(
		array(
			new ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation(),
			new ILIAS\Refinery\KindlyTo\Transformation\StringTransformation(),
		)
	);

	$result = $transformation->transform(5.3);

	return assert(array(5, '5.3'), $result);
}
