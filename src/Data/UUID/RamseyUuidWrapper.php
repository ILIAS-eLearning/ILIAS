<?php

namespace ILIAS\Data\UUID;


use Ramsey\Uuid\UuidInterface as RamseyUuidInterface;

/**
 * Class Uuid
 * @package ILIAS\Data\UUID
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class RamseyUuidWrapper implements Uuid {

	/**
	 * @var RamseyUuidInterface
	 */
	private $wrapped_uuid;


	/**
	 * Uuid constructor.
	 * @param RamseyUuidInterface $wrapped_uuid
	 */
	public function __construct(RamseyUuidInterface $wrapped_uuid) {
		$this->wrapped_uuid = $wrapped_uuid;
	}

	/**
	 * @return RamseyUuidInterface
	 */
	public function getWrappedUuid(): RamseyUuidInterface {
		return $this->wrapped_uuid;
	}

	/**
	 * @param RamseyUuidWrapper $other
	 * @return int
	 */
	public function compareTo(Uuid $other): int {
		return $this->wrapped_uuid->compareTo($other->getWrappedUuid());
	}

	/**
	 * @param RamseyUuidWrapper $other
	 * @return bool
	 */
	public function equals(Uuid $other): bool {
		return $this->wrapped_uuid->equals($other->getWrappedUuid());
	}

	/**
	 * @return string
	 */
	public function toString(): string {
		return $this->wrapped_uuid->toString();
	}

}