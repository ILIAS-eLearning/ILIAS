<?php
/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

namespace ILIAS\KioskMode;

/**
 * Keeps the state of a view in a simple stringly type key-value store.
 */
class State {
	/**
	 * Set a value for a key of the state.
	 */
	public function withValueFor(string $key, string $value) : State {
	}

	/**
	 * Remove the key-value-pair.
	 */
	public function withoutKey(string $key) : State {
	}

	/**
	 * Get the value for the given key.
	 */
	public function getValueFor(string $key) : State {
	}
}
