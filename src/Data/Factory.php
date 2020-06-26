<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;

use ILIAS\Data\Interval\OpenedFloatInterval;
use ILIAS\Data\Interval\OpenedIntegerInterval;
use ILIAS\Data\Interval\ClosedFloatInterval;
use ILIAS\Data\Interval\ClosedIntegerInterval;

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
    private $colorfactory;

    /**
     * Get an ok result.
     *
     * @param  mixed  $value
     * @return Result
     */
    public function ok($value)
    {
        return new Result\Ok($value);
    }

    /**
     * Get an error result.
     *
     * @param  string|\Exception $error
     * @return Result
     */
    public function error($e)
    {
        return new Result\Error($e);
    }

    /**
     * Color is a data type representing a color in HTML.
     * Construct a color with a hex-value or list of RGB-values.
     *
     * @param  string|int[] 	$value
     * @return Color
     */
    public function color($value)
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
     *
     * @param  string	$uri_string
     * @return URI
     */
    public function uri($uri_string)
    {
        return new URI($uri_string);
    }

    /**
     * Represents the size of some data.
     *
     * @param	string|int	$size	string might be a string like "126 MB"
     * @throw	\InvalidArgumentException if first argument is int and second is not a valid unit.
     * @throw	\InvalidArgumentException if string size can't be interpreted
     */
    public function dataSize($size, string $unit = null) : DataSize
    {
        if (is_string($size)) {
            $match = [];
            if (!preg_match("/(\d+)\s*([a-zA-Z]+)/", $size, $match)) {
                throw \InvalidArgumentException("'$size' can't be interpreted as data size.");
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

    /**
     * Get a password.
     *
     * @param  string
     * @return Password
     */
    public function password($pass)
    {
        return new Password($pass);
    }

    /**
     * @param string $clientId
     * @return ClientId
     */
    public function clientId(string $clientId) : ClientId
    {
        return new ClientId($clientId);
    }

    /**
     * @param int $ref_id
     *
     * @return ReferenceId
     */
    public function refId(int $ref_id) : ReferenceId
    {
        return new ReferenceId($ref_id);
    }

    /**
     * @param $value
     * @return Alphanumeric
     */
    public function alphanumeric($value) : Alphanumeric
    {
        return new Alphanumeric($value);
    }

    /**
     * @param int $value
     * @return PositiveInteger
     */
    public function positiveInteger(int $value) : PositiveInteger
    {
        return new PositiveInteger($value);
    }

    /**
     * @param int $minimum
     * @param int $maximum
     * @return OpenedIntegerInterval
     */
    public function openedIntegerInterval(int $minimum, int $maximum) : OpenedIntegerInterval
    {
        return new OpenedIntegerInterval($minimum, $maximum);
    }

    /**
     * @param int $minimum
     * @param int $maximum
     * @return ClosedIntegerInterval
     */
    public function closedIntegerInterval(int $minimum, int $maximum) : ClosedIntegerInterval
    {
        return new ClosedIntegerInterval($minimum, $maximum);
    }

    /**
     * @param float $minimum
     * @param float $maximum
     * @return Float
     */
    public function openedFloatInterval(float $minimum, float $maximum) : OpenedFloatInterval
    {
        return new OpenedFloatInterval($minimum, $maximum);
    }

    /**
     * @param float $minimum
     * @param float $maximum
     * @return ClosedFloatInterval
     */
    public function closedFloatInterval(float $minimum, float $maximum) : ClosedFloatInterval
    {
        return new ClosedFloatInterval($minimum, $maximum);
    }

    /**
     * @return DateFormat\Factory
     */
    public function dateFormat() : DateFormat\Factory
    {
        $builder = new DateFormat\FormatBuilder();
        return new DateFormat\Factory($builder);
    }
}
