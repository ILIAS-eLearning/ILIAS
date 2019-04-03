<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function toRecord() {
	global $DIC;

	$refinery = $DIC->refinery();

	$transformation = $refinery->to()->recordOf(
		array(
			'user_id' => new \ILIAS\Refinery\To\Transformation\IntegerTransformation(),
			'points'  => new \ILIAS\Refinery\To\Transformation\IntegerTransformation()
		)
	);

	$result = $transformation->transform(array('user_id' => 5, 'points' => 1));

	return assert(array('user_id' => 5, 'points' => 1) === $result);
}
