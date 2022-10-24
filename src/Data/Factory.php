<?php

declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;

use ILIAS\Data\Clock\ClockFactory;
use ILIAS\Data\Clock\ClockFactoryImpl;

/**
 * Builds data types.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class Factory
{
    /**
     * cache for color factory.
     */
    private ?Color\Factory $colorfactory = null;
    private ?Dimension\Factory $dimensionfactory = null;

    /**
     * Get an ok result.
     *
     * @param mixed $value
     */
    public function ok($value): Result
    {
        return new Result\Ok($value);
    }

    /**
     * Get an error result.
     *
     * @param string|\Exception $e
     * @return Result
     */
    public function error($e): Result
    {
        return new Result\Error($e);
    }

    /**
     * Color is a data type representing a color in HTML.
     * Construct a color with a hex-value or list of RGB-values.
     *
     * @param string|int[] $value
     */
    public function color($value): Color
    {
        if (!$this->colorfactory) {
            $this->colorfactory = new Color\Factory();
        }
        return $this->colorfactory->build($value);
    }

    /**
     * Object representing an uri valid according to RFC 3986
     * with restrictions imposed on valid characters and obliagtory
     * parts.
     */
    public function uri(string $uri_string): URI
    {
        return new URI($uri_string);
    }

    /**
     * Represents the size of some data.
     *
     * @param string|int $size string might be a string like "126 MB"
     * @throw   \InvalidArgumentException if first argument is int and second is not a valid unit.
     * @throw   \InvalidArgumentException if string size can't be interpreted
     */
    public function dataSize($size, string $unit = null): DataSize
    {
        if (is_string($size)) {
            $match = [];
            if (!preg_match("/(\d+)\s*([a-zA-Z]+)/", $size, $match)) {
                throw new \InvalidArgumentException("'$size' can't be interpreted as data size.");
            }
            return $this->dataSize((int) $match[1], $match[2]);
        }
        if (is_int($size) && (is_null($unit) || !array_key_exists($unit, DataSize::$abbreviations))) {
            throw new \InvalidArgumentException(
                "Expected second argument to be a unit for data, '$unit' is unknown."
            );
        }
        $unit_size = DataSize::$abbreviations[$unit];
        return new DataSize($size * $unit_size, $unit_size);
    }

    public function password(string $pass): Password
    {
        return new Password($pass);
    }

    public function clientId(string $clientId): ClientId
    {
        return new ClientId($clientId);
    }

    public function refId(int $ref_id): ReferenceId
    {
        return new ReferenceId($ref_id);
    }

    public function objId(int $obj_id): ObjectId
    {
        return new ObjectId($obj_id);
    }

    /**
     * @param mixed $value
     */
    public function alphanumeric($value): Alphanumeric
    {
        return new Alphanumeric($value);
    }

    public function positiveInteger(int $value): PositiveInteger
    {
        return new PositiveInteger($value);
    }

    public function dateFormat(): DateFormat\Factory
    {
        $builder = new DateFormat\FormatBuilder();
        return new DateFormat\Factory($builder);
    }

    public function range(int $start, int $length): Range
    {
        return new Range($start, $length);
    }

    /**
     * @param string $direction Order::ASC|Order::DESC
     */
    public function order(string $subject, string $direction): Order
    {
        return new Order($subject, $direction);
    }

    /**
     * @param string $version in the form \d+([.]\d+([.]\d+)?)?
     * @throws  \InvalidArgumentException if version string does not match \d+([.]\d+([.]\d+)?)?
     */
    public function version(string $version): Version
    {
        return new Version($version);
    }

    public function link(string $label, URI $url): Link
    {
        return new Link($label, $url);
    }

    public function clock(): ClockFactory
    {
        return new ClockFactoryImpl();
    }

    public function dimension(): Dimension\Factory
    {
        if (!$this->dimensionfactory) {
            $this->dimensionfactory = new Dimension\Factory();
        }
        return $this->dimensionfactory;
    }

    /**
     * @param array<string, Dimension\Dimension> $dimensions Dimensions with their names as keys
     */
    public function dataset(array $dimensions): Chart\Dataset
    {
        return new Chart\Dataset($dimensions);
    }
}
