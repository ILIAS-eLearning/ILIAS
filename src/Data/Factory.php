<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;

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
	 * @param string $clientId
	 * @return ClientId
	 */
	public function clientId($clientId)
	{
		return new ClientId($clientId);
	}
}
