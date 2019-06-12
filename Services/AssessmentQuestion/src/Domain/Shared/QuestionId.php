<?php

namespace ILIAS\AssessmentQuestion\Domainmodel\Common;

use ILIAS\Data\Domain\IdentifiesAggregate;

class QuestionId implements IdentifiesAggregate {

	private $aggregate_id;
	public function __construct($aggregate_id)
	{
		$this->aggregate_id = (string) $aggregate_id;
	}
	public static function fromString(string $string): IdentifiesAggregate {
		return new QuestionId($string);
	}
	/**
	 * Returns a string that can be parsed by fromString()
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) $this->aggregate_id;
	}
	public function equals(IdentifiesAggregate $other): bool
	{
		return $this->aggregate_id === $other;
	}
	public static function generate()
	{
		//TODO real guid
		return new QuestionId(
			(string) substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,10));
	}
}