<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
function toList() {
	class SomeClass {
		public function say(string $firstWord, string $secondWord) {
			return $firstWord . $secondWord;
		}
	}

	global $DIC;

	$instance = new SomeClass();

	$refinery = $DIC->refinery();

	$transformation = $refinery->to()->toNew(
		array($instance, 'say')
	);

	$result = $transformation->transform(array('Hello', ' World!'));

	return assert('Hello World!' === $result);
}
