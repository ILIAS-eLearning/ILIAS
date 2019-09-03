<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * An array as an artifact.
 */
class ArrayArtifact implements Artifact {
	/**
	 * @var array
	 */
	private $data = [];

	/**
	 * @param array  $data - may only contain primitive data
	 */
	public function __construct(array $data) {
		$this->check($data);
		$this->data = $data;
	}


	public final function serialize() : string {
		return "<?"."php return " . var_export($this->data, true) . ";";
	}

	private function check(array $a) {
		foreach ($a as $item) {
			if (is_string($item) || is_int($item) || is_float($item) || is_bool($item) || is_null($item)) {
				continue;
			}
			if (is_array($item)) {
				$this->check($item);
				continue;
			}
			throw new \InvalidArgumentException(
				"Array data for artifact may only contain ints, strings, floats, bools or ".
				"other arrays with this content. Found: ".gettype($item)
			);
		}
	}
}
