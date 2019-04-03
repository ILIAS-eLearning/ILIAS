<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;
use ILIAS\Data\Range\FloatRange;
use ILIAS\Data\Range\IntegerRange;
use ILIAS\Data\Range\StrictFloatRange;
use ILIAS\Data\Range\StrictIntegerRange;

/**
 * Builds data types.
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class Factory {
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
	public function ok($value) {
		return new Result\Ok($value);
	}

	/**
	 * Get an error result.
	 *
	 * @param  string|\Exception $error
	 * @return Result
	 */
	public function error($e) {
		return new Result\Error($e);
	}

	/**
	 * Color is a data type representing a color in HTML.
	 * Construct a color with a hex-value or list of RGB-values.
	 *
	 * @param  string|int[] 	$value
	 * @return Color
	 */
	public function color($value) {
		if(! $this->colorfactory) {
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
	public function clientId(string $clientId): ClientId
	{
		return new ClientId($clientId);
	}


	/**
	 * @param int $ref_id
	 *
	 * @return ReferenceId
	 */
	public function refId(int $ref_id): ReferenceId {
		return new ReferenceId($ref_id);
	}

	/**
	 * @param $value
	 * @return Alphanumeric
	 */
	public function alphanumeric($value): Alphanumeric
	{
		return new Alphanumeric($value);
	}

	/**
	 * @param int $value
	 * @return PositiveInteger
	 */
	public function positiveInteger(int $value): PositiveInteger
	{
		return new PositiveInteger($value);
	}

	/**
	 * @param int $minimum
	 * @param int $maximum
	 * @return IntegerRange
	 */
	public function integerRange(int $minimum, int $maximum): IntegerRange
	{
		return new IntegerRange($minimum, $maximum);
	}

	/**
	 * @param int $minimum
	 * @param int $maximum
	 * @return StrictIntegerRange
	 */
	public function strictIntegerRange(int $minimum, int $maximum): StrictIntegerRange
	{
		return new StrictIntegerRange($minimum, $maximum);
	}

	/**
	 * @param float $minimum
	 * @param float $maximum
	 * @return FloatRange
	 */
	public function floatRange(float $minimum, float $maximum): FloatRange
	{
		return new FloatRange($minimum, $maximum);
	}

	/**
	 * @param float $minimum
	 * @param float $maximum
	 * @return StrictFloatRange
	 */
	public function strictFloatRange(float $minimum, float $maximum): StrictFloatRange
	{
		return new StrictFloatRange($minimum, $maximum);
	}
}
