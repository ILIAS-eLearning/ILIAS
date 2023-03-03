<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Data;

/**
 * This class provides the data size with additional information to
 * remove the work to calculate the size to different unit like GiB, GB usw.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class DataSize
{
    private const SIZE_FACTOR = 1000;
    private const PRECISION = 2;
    public const Byte = 1;
    // binary
    public const KiB = 1024;
    public const MiB = 1_048_576;
    // pow(1024, 2)
    public const GiB = 1_073_741_824;
    public const TiB = 1_099_511_627_776;
    // decimal
    public const KB = 1000;
    // kilobyte
    public const MB = 1_000_000;
    // megabyte
    public const GB = 1_000_000_000;
    // gigabyte
    public const TB = 1_000_000_000_000;
    // terabyte
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
     */
    public function __construct(int $size, int $unit)
    {
        $this->suffix = $this->mapUnitToSuffix($unit);
        $this->size = (float) $size / (float) $unit; // the div operation can return int and float
        $this->unit = $unit;
    }
    private function mapUnitToSuffix(int $unit): string
    {
        switch ($unit) {
            case self::Byte:
                return 'B';
            case self::KiB:
                return 'KiB';
            case self::MiB:
                return 'MiB';
            case self::GiB:
                return 'GiB';
            case self::TiB:
                return 'TiB';
            case self::KB:
                return 'KB';
            case self::MB:
                return 'MB';
            case self::GB:
                return 'GB';
            case self::TB:
                return 'TB';
            default:
                throw new \InvalidArgumentException('The given data size unit is not valid, please check the provided class constants of the DataSize class.');
        }
    }
    /**
     * The calculated data size.
     */
    public function getSize(): float
    {
        return $this->size;
    }
    /**
     * The unit which equals the class constant used to calculate the data size. (self::GiB, ...)
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
     * Returns the data size in a human readable manner.
     *
     * Example output:
     * 950 B
     * 3922 GB
     */
    public function __toString(): string
    {
        $size = $this->inBytes();
        // can be switched to match in ILIAS 9
        switch (true) {
            case $size > self::SIZE_FACTOR * self::SIZE_FACTOR * self::SIZE_FACTOR * self::SIZE_FACTOR:
                $unit = DataSize::TB;
                break;
            case $size > self::SIZE_FACTOR * self::SIZE_FACTOR * self::SIZE_FACTOR:
                $unit = DataSize::GB;
                break;
            case $size > self::SIZE_FACTOR * self::SIZE_FACTOR:
                $unit = DataSize::MB;
                break;
            case $size > self::SIZE_FACTOR:
                $unit = DataSize::KB;
                break;
            default:
                $unit = DataSize::Byte;
                break;
        }

        $size = round($size / (float) $unit, self::PRECISION);

        return "$size " . $this->mapUnitToSuffix($unit);
    }
}
