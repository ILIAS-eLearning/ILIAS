<?php

namespace ILIAS\Messaging\Example\ExampleCourse\Domainmodel\Aggregate;

use ILIAS\Data\Domain\IdentifiesAggregate;

class CourseId implements IdentifiesAggregate {

	private $aggregate_id;
	public function __construct($aggregate_id)
	{
		$this->aggregate_id = (string) $aggregate_id;
	}
	public static function fromString(string $string): IdentifiesAggregate {
		return new CourseId($string);
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
		return new CourseId(
			(string) Uuid::uuid1()
		);
	}
}