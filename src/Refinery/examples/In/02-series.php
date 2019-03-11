<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function series() {
	global $DIC;

	$language = $DIC->language();
	$dataFactory = new ILIAS\Data\Factory();
	$validationFactory = new \ILIAS\Refinery\Validation\Factory($dataFactory, $language);

	$factory = new ILIAS\Refinery\BasicFactory($validationFactory);

	$transformation = $factory->in()->series(
		array(
			new ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation(),
			new ILIAS\Refinery\KindlyTo\Transformation\StringTransformation(),
		)
	);

	$result = $transformation->transform(5.3);

	return assert('5' === $result);
}
