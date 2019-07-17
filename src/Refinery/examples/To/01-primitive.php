<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function primitive() {
	global $DIC;

	$refinery = $DIC->refinery();

	$transformation = $refinery->to()->int();

	$result = $transformation->transform(5);

	return assert(5 === $result);
}
