<?php

namespace ILIAS\Refinery\String;

use ILIAS\Data\Factory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation;
use InvalidArgumentException;
use Throwable;

/**
 * Class TitleCapitalization
 *
 * Format a text for the title capitalization presentation (Specification at https://docu.ilias.de/goto_docu_pg_1430_42.html)
 *
 * @package ILIAS\Refinery\String
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class TitleCapitalization implements Transformation {

	/**
	 * @var Factory
	 */
	protected $factory;
	/**
	 * @var array
	 */
	protected $not_capitalize = [
		// conjunctions
		"after",
		"although",
		"and",
		"as far as",
		"as how",
		"as if",
		"as long as",
		"as soon as",
		"as though",
		"as well as",
		"as",
		"because",
		"before",
		"both",
		"but",
		"either",
		"even if",
		"even though",
		"for",
		"how",
		"however",
		"if only",
		"in case",
		"in order that",
		"if",
		"neither",
		"nor",
		"once",
		"only",
		"or",
		"now",
		"provided",
		"rather",
		"than",
		"since",
		"so that",
		"so",
		"than",
		"that",
		"though",
		"till",
		"unless",
		"until",
		"when",
		"whenever",
		"where as",
		"wherever",
		"whether",
		"while",
		"where",
		"yet",
		// prepositions
		"about",
		"above",
		"according to",
		"across",
		"after",
		"against",
		"along with",
		"along",
		"among",
		"apart from",
		"around",
		"as for",
		"as",
		"at",
		"because of",
		"before",
		"behind",
		"below",
		"beneath",
		"beside",
		"between",
		"beyond",
		"but",
		"by means of",
		"by",
		"concerning",
		"despite",
		"down",
		"during",
		"except for",
		"excepting",
		"except",
		"for",
		"from",
		"in addition to",
		"in back of",
		"in case of",
		"in front of",
		"in place of",
		"inside",
		"in spite of",
		"instead of",
		"into",
		"in",
		"like",
		"near",
		"next",
		"off",
		"onto",
		"on top of",
		"out out of",
		"outside",
		"over",
		"past",
		"regarding",
		"round",
		"on",
		"of",
		"since",
		"through",
		"throughout",
		"till",
		"toward",
		"under",
		"underneath",
		"unlike",
		"until",
		"upon",
		"up to",
		"up",
		"to",
		"with",
		"within",
		"without",
		// articles
		"a",
		"an",
		"few",
		"some",
		"the",
		"one",
		"this",
		"that"
	];


	/**
	 * TitleCapitalization constructor
	 *
	 * @param Factory $factory
	 */
	public function __construct(Factory $factory) {
		$this->factory = $factory;
	}


	/**
	 * @inheritdoc
	 */
	public function transform($from): string {
		if (!is_string($from)) {
			throw new InvalidArgumentException(__METHOD__ . " the argument is not a string.");
		}

		$to = preg_replace_callback("/[\w]+/", [ $this, "replaceHelper" ], $from);

		return $to;
	}


	/**
	 * @inheritdoc
	 */
	public function applyTo(Result $data): Result {
		$dataValue = $data->value();

		try {
			$value = $this($dataValue);

			return $this->factory->ok($value);
		} catch (Throwable $ex) {
			return $this->factory->error($ex);
		}
	}


	/**
	 * @inheritdoc
	 */
	public function __invoke($from): string {
		return $this->transform($from);
	}


	/**
	 * @param array $result
	 *
	 * @return string
	 */
	protected function replaceHelper(array $result): string {
		$word = strtolower($result[0] ?? "");
echo $word;
		if (!in_array($word, $this->not_capitalize)) {
			return ucwords($word);
		} else {
			return $word;
		}
	}
}
