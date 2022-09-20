<?php

declare(strict_types=1);

namespace ILIAS\Data;

/**
 * Class DataSize
 *
 * This class provides the data size with additional information to
 * remove the work to calculate the size to different unit like GiB, GB usw.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 */
final class DataSize
{
    public const Byte = 1;

    //binary
    public const KiB = 1024;
    public const MiB = 1048576;      //pow(1024, 2)
    public const GiB = 1073741824;
    public const TiB = 1099511627776;
    public const PiB = 1125899906842624;
    public const EiB = 1152921504606846976;
    public const ZiB = 1180591620717411303424;
    public const YiB = 1208925819614629174706176;

    //decimal
    public const KB = 1000;                            //kilobyte
    public const MB = 1000000;                         //megabyte
    public const GB = 1000000000;                      //gigabyte
    public const TB = 1000000000000;                   //terabyte
    public const PB = 1000000000000000;                //petabyte
    public const EB = 1000000000000000000;             //exabyte
    public const ZB = 1000000000000000000000;          //zettabyte
    public const YB = 1000000000000000000000000;       //yottabyte
    /**
     * @var array<int, string>
     */
    private static array $suffixMap = [
        self::Byte => 'B',

        self::KB => 'KB',
        self::KiB => 'KiB',

        self::MB => 'MB',
        self::MiB => 'MiB',

        self::GB => 'GB',
        self::GiB => 'GiB',

        self::TB => 'TB',
        self::TiB => 'TiB',

        self::PB => 'PB',
        self::PiB => 'PiB',

        self::EB => 'EB',
        self::EiB => 'EiB',

        self::ZB => 'ZB',
        self::ZiB => 'ZiB',

        self::YB => 'YB',
        self::YiB => 'YiB'
    ];

    /**
     * @var array<string, int>
     */
    public static array $abbreviations = [
        'B' => self::Byte,

        'KB' => self::KB,
        'K' => self::KiB,
        'k' => self::KiB,
        'KiB' => self::KiB,

        'MB' => self::MB,
        'M' => self::MiB,
        'm' => self::MiB,
        'MiB' => self::MiB,

        'GB' => self::GB,
        'G' => self::GiB,
        'g' => self::GiB,
        'GiB' => self::GiB,

        'TB' => self::TB,
        'TiB' => self::TiB,

        'PB' => self::PB,
        'PiB' => self::PiB,

        'EB' => self::EB,
        'EiB' => self::EiB,

        'ZB' => self::ZB,
        'ZiB' => self::ZiB,

        'YB' => self::YB,
        'YiB' => self::YiB
    ];

    private float $size;
    private int $unit;
    private string $suffix;

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
    public function __construct(int $size, int $unit)
    {
        if (!isset(self::$suffixMap[$unit])) {
            throw new \InvalidArgumentException('The given data size unit is not valid, please check the provided class constants of the DataSize class.');
        }

        $this->size = (float) $size / $unit; //the div operation can return int and float
        $this->unit = $unit;
        $this->suffix = self::$suffixMap[$unit];
    }

    /**
     * The calculated data size.
     *
     * @since 5.3
     */
    public function getSize(): float
    {
        return $this->size;
    }

    /**
     * The unit which equals the class constant used to calculate the data size. (self::GiB, ...)
     *
     * @since 5.3
     */
    public function getUnit(): int
    {
        return $this->unit;
    }

    /**
     * Get the size in bytes.
     */
    public function inBytes(): float
    {
        return $this->size * $this->unit;
    }

    /**
     * Returns the data size with the corresponding suffix.
     *
     * Example output:
     * 1024 B
     * 4096 GiB
     *
     * @since 5.3
     */
    public function __toString(): string
    {
        return "$this->size $this->suffix";
    }
}
