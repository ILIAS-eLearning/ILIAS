<?php
/* Copyright (c) 2019 Martin Studer <ms@studer-raimann.ch> Extended GPL, see docs/LICENSE - inspired by https://github.com/buttercup-php/protects */

namespace ILIAS\Data\Domain\Entity;

/**
 * An object that identifies an Aggregate. Typically a UUID, but any kind of id will do, as long as it is unique within the system.
 */


/**
 * Creates an identifier object from a string representation
 *
 * @param string $string
 *
 * @return AggregateId
 */
interface AggregateId
{
	public function id();
	public function equals(AggregateId $anId);
}