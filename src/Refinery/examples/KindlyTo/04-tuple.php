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

	$transformation = $factory->kindlyTo()->tupleOf(
		array(
			new \ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation(),
			new \ILIAS\Refinery\KindlyTo\Transformation\StringTransformation()
		)
	);

	$result = $transformation->transform(array(5.3, 2));

	return assert(array(5, 2) === $result);
}
