<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function parallel() {
	global $DIC;

	$language = $DIC->language();
	$dataFactory = new ILIAS\Data\Factory();
	$validationFactory = new \ILIAS\Refinery\Validation\Factory($dataFactory, $language);

	$factory = new ILIAS\Refinery\BasicFactory($validationFactory);

	$transformation = $factory->in()->parallel(
		array(
			new ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation(),
			new ILIAS\Refinery\KindlyTo\Transformation\StringTransformation(),
		)
	);

	$result = $transformation->transform(5.3);

	return $result;
}

echo parallel();
