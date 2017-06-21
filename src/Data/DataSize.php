<?php

namespace ILIAS\Data;

/**
 * Class DataSize
 *
 * This class provides the data size with additional information to
 * remove the work to calculate the size to different unit like GiB, GB usw.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 */
final class DataSize {

	const Byte = 1;

	//binary
	const KiB = 1024;
	const MiB = 1024 ** 2;      //pow(1024, 2)
	const GiB = 1024 ** 3;
	const TiB = 1024 ** 4;
	const PiB = 1024 ** 5;
	const EiB = 1024 ** 6;
	const ZiB = 1024 ** 7;
	const YiB = 1024 ** 8;

	//decimal
	const KB = 1000;            //kilobyte
	const MB = 1000 ** 2;       //megabyte
	const GB = 1000 ** 3;       //gigabyte
	const TB = 1000 ** 4;       //terabyte
	const PB = 1000 ** 5;       //petabyte
	const EB = 1000 ** 6;       //exabyte
	const ZB = 1000 ** 7;       //zettabyte
	const YB = 1000 ** 8;       //yottabyte
	/**
	 * @var string[] $suffixMap
	 */
	private static $suffixMap = [
		self::Byte  => 'B',

		self::KB    => 'KB',
		self::KiB   => 'KiB',

		self::MB    => 'MB',
		self::MiB   => 'MiB',

		self::GB    => 'GB',
		self::GiB   => 'GiB',

		self::TB    => 'TB',
		self::TiB   => 'TiB',

		self::PB    => 'PB',
		self::PiB   => 'PiB',

		self::EB    => 'EB',
		self::EiB   => 'EiB',

		self::ZB    => 'ZB',
		self::ZiB   => 'ZiB',

		self::YB    => 'YB',
		self::YiB   => 'YiB'
	];
	/**
	 * @var float $size
	 */
	private $size;
	/**
	 * @var int $unit
	 */
	private $unit;
	/**
	 * @var string $suffix
	 */
	private $suffix;

	/**
	 * DataSize constructor.
	 *
	 * @param int $size The data size in bytes.
	 * @param int $unit The unit which is used to calculate the data size.
	 *
	 * @throws \InvalidArgumentException If the given unit is not valid or the arguments are not of the type int.
	 *
	 * @since 5.3
	 */
	public function __construct($size, $unit) {

		if(!is_int($size))
			throw new \InvalidArgumentException("Size must be of the type int.");

		if(!is_int($unit))
			throw new \InvalidArgumentException("Unit must be of the type int.");

		if(!isset(self::$suffixMap[$unit]))
			throw new \InvalidArgumentException('The given data size unit is not valid, please check the provided class constants of the DataSize class.');

		$this->size = (float)$size / $unit; //the div operation can return int and float
		$this->unit = $unit;
		$this->suffix = self::$suffixMap[$unit];
	}

	/**
	 * The calculated data size.
	 *
	 * @return float
	 * @since 5.3
	 */
	public function getSize() {
		return $this->size;
	}


	/**
	 * The unit which equals the class constant used to calculate the data size. (self::GiB, ...)
	 *
	 * @return int
	 * @since 5.3
	 */
	public function getUnit() {
		return $this->unit;
	}

	/**
	 * Returns the data size with the corresponding suffix.
	 *
	 * Example output:
	 * 1024 B
	 * 4096 GiB
	 *
	 * @return string
	 * @since 5.3
	 */
	function __toString() {
		return "{$this->size} {$this->suffix}";
	}
}