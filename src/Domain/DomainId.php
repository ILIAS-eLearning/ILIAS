<?php

namespace ILIAS\Domain;

interface DomainId {

	public function __construct(string $id = null);

	/**
	 * @internal
	 */
	public function __toString(): string;

	/**
	 * @return static
	 */
	public static function fromValue($value): DomainId;

	public function isEmpty(): bool;

	public function equals(DomainId $id): bool;

	public function toString(): string;
}