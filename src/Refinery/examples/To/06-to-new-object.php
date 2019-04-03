<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function toNewObject() {
	class SomeClass {
		private $firstParameter;
		private $secondParameter;
		private $thirdParameter;

		public function __construct(
			string $firstParameter,
			int $secondParameter,
			string $thirdParameter
		) {
			$this->firstParameter = $firstParameter;
			$this->secondParameter = $secondParameter;
			$this->thirdParameter = $thirdParameter;
		}

		public function say() {
			return $this->firstParameter;
		}
	}

	global $DIC;

	$language = $DIC->language();
	$dataFactory = new ILIAS\Data\Factory();
	$validationFactory = new \ILIAS\Refinery\Validation\Factory($dataFactory, $language);

	$factory = new ILIAS\Refinery\BasicFactory($validationFactory);

	$transformation = $factory->to()->toNew(
		'SomeClass'
	);

	$result = $transformation->transform(array('firstParameter', 2, 'thirdParameter'));

	return assert('firstParameter' === $result->say());
}
