<?php

namespace ILIAS\Data\UUID;


use Ramsey\Uuid\UuidInterface as RamseyUuidInterface;

/**
 * Class Uuid
 * @package ILIAS\Data\UUID
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class RamseyUuidWrapper implements UuidInterface {

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
	public function compareTo(UuidInterface $other): int {
		return $this->wrapped_uuid->compareTo($other->getWrappedUuid());
	}

	/**
	 * @param RamseyUuidWrapper $other
	 * @return bool
	 */
	public function equals(UuidInterface $other): bool {
		return $this->wrapped_uuid->equals($other->getWrappedUuid());
	}

	/**
	 * @return string
	 */
	public function getBytes(): string {
		return $this->wrapped_uuid->getBytes();
	}

	/**
	 * @return string
	 */
	public function getHex(): string {
		return $this->wrapped_uuid->getHex();
	}

	/**
	 * @return array
	 */
	public function getFieldsHex(): array {
		return $this->wrapped_uuid->getFieldsHex();
	}

	/**
	 * @return string
	 */
	public function getClockSeqHiAndReservedHex(): string {
		return $this->wrapped_uuid->getClockSeqHiAndReservedHex();
	}

	/**
	 * @return string
	 */
	public function getClockSeqLowHex(): string {
		return $this->wrapped_uuid->getClockSeqLowHex();
	}

	/**
	 * @return string
	 */
	public function getClockSequenceHex(): string {
		return $this->wrapped_uuid->getClockSequenceHex();
	}

	/**
	 * @return int
	 */
	public function getInteger(): int {
		return $this->wrapped_uuid->getInteger();
	}

	/**
	 * @return string
	 */
	public function getLeastSignificantBitsHex(): string {
		return $this->wrapped_uuid->getLeastSignificantBitsHex();
	}

	/**
	 * @return string
	 */
	public function getMostSignificantBitsHex(): string {
		return $this->wrapped_uuid->getMostSignificantBitsHex();
	}

	/**
	 * @return string
	 */
	public function getNodeHex(): string {
		return $this->wrapped_uuid->getNodeHex();
	}

	/**
	 * @return string
	 */
	public function getTimeHiAndVersionHex(): string {
		return $this->wrapped_uuid->getTimeHiAndVersionHex();
	}

	/**
	 * @return string
	 */
	public function getTimeLowHex(): string {
		return $this->wrapped_uuid->getTimeLowHex();
	}

	/**
	 * @return string
	 */
	public function getTimeMidHex(): string {
		return $this->wrapped_uuid->getTimeMidHex();
	}

	/**
	 * @return string
	 */
	public function getUrn(): string {
		return $this->wrapped_uuid->getUrn();
	}

	/**
	 * @return int
	 */
	public function getVariant(): int {
		return $this->wrapped_uuid->getVariant();
	}

	/**
	 * @return int
	 */
	public function getVersion(): int {
		return $this->wrapped_uuid->getVersion();
	}

	/**
	 * @return string
	 */
	public function toString(): string {
		return $this->wrapped_uuid->toString();
	}

}