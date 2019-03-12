<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function toRecord() {
	global $DIC;

	$language = $DIC->language();
	$dataFactory = new ILIAS\Data\Factory();
	$validationFactory = new \ILIAS\Refinery\Validation\Factory($dataFactory, $language);

	$factory = new ILIAS\Refinery\BasicFactory($validationFactory);

	$transformation = $factory->kindlyTo()->recordOf(
		array(
			'user_id' => new \ILIAS\Refinery\KindlyTo\Transformation\StringTransformation(),
			'points'  => new \ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation()
		)
	);

	$result = $transformation->transform(array('user_id' => 5, 'points' => 4.3));

	return assert(array('user_id' => '5', 'points' => 4) === $result);
}
