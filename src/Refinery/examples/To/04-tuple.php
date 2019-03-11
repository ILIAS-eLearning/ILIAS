<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function toTuple() {
	global $DIC;

	$language = $DIC->language();
	$dataFactory = new ILIAS\Data\Factory();
	$validationFactory = new \ILIAS\Refinery\Validation\Factory($dataFactory, $language);

	$factory = new ILIAS\Refinery\BasicFactory($validationFactory);

	$transformation = $factory->to()->tupleOf(
		array(
			new \ILIAS\Refinery\To\Transformation\IntegerTransformation(),
			new \ILIAS\Refinery\To\Transformation\IntegerTransformation()
		)
	);

	$result = $transformation->transform(array(5, 1));

	return assert(array(5, 1) === $result);
}
