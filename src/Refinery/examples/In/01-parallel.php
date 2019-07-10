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
			new ILIAS\Refinery\To\Transformation\IntegerTransformation(),
			new ILIAS\Refinery\To\Transformation\IntegerTransformation(),
		)
	);

	$result = $transformation->transform(5);

	return assert(array(5, 5), $result);
}
