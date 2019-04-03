<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function series() {
	global $DIC;

	$refinery = $DIC->refinery();

	$transformation = $refinery->in()->series(
		array(
			new ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation(),
			new ILIAS\Refinery\KindlyTo\Transformation\StringTransformation(),
		)
	);

	$result = $transformation->transform(5.3);

	return assert('5' === $result);
}
