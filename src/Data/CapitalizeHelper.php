<?php

namespace ILIAS\Data;

/**
 * Trait CapitalizeHelper
 *
 * @package ILIAS\Data
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
trait CapitalizeHelper {

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	protected function capitalizeFirstLetterOfWord(string $text): string {
		// Title case means that the first letter of each word is capitalized, except for certain small words, such as articles and short prepositions (@see https://docu.ilias.de/goto_docu_pg_1430_42.html)
		$words_not_to_capitalize = [
			"that",
			"this"
		];

		return preg_replace_callback("/\w+/", function (array $result) use (&$words_not_to_capitalize): string {
			$word = $result[0] ?? "";

			if (!in_array($word, $words_not_to_capitalize)) {
				return ucfirst($word);
			} else {
				return $word;
			}
		}, $text);
	}
}
