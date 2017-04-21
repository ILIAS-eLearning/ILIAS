<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\DTO;

use ILIAS\Filesystem\Exception\IllegalArgumentException;

/**
 * Class FileSize
 *
 * This class provides the file size with additional information to
 * remove the work to calculate the size to different unit like GiB, GB usw.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 */
final class FileSize {

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
	 * FileSize constructor.
	 *
	 * @param int $size The file size in bytes.
	 * @param int $unit The unit which is used to calculate the file size.
	 *
	 * @throws IllegalArgumentException If the given unit is not valid, or the file size is negative.
	 *
	 * @since 5.3
	 * @version 1.0
	 */
	public function __construct(int $size, int $unit) {

		if(!isset(self::$suffixMap[$unit]))
			throw new IllegalArgumentException('The given file size unit is not valid, please check the provided class constants of the FileSize class.');

		if($size < 0)
			throw new IllegalArgumentException('The file size must not be negative.');

		$this->size = (float)$size / $unit; //the div operation can return int and float
		$this->unit = $unit;
		$this->suffix = self::$suffixMap[$unit];
	}

	/**
	 * The calculated file size.
	 *
	 * @return float
	 */
	public function getSize() : float {
		return $this->size;
	}


	/**
	 * The unit which equals the class constant used to calculate the file size. (self::GiB, ...)
	 *
	 * @return int
	 */
	public function getUnit(): int {
		return $this->unit;
	}


	/**
	 * The string suffix of the unit. (GB, GiB, ...)
	 *
	 * @return string
	 */
	public function getSuffix() : string {
		return $this->suffix;
	}
}