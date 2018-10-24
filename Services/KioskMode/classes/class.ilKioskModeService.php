<?php
/* Copyright (c) 2018 - Richard Klees <richard.klees@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Central entry point for users of the service.
 */
final class ilKioskModeService {
	/**
	 * Try to get a kiosk mode view for the given object.
	 *
	 * @return	ilKioskModeView|null
	 */
	public function getViewFor(\ilObject $object) {
	}

	/**
	 * Check if objects of a certain type provide kiosk modes in general.
	 *
	 * @param	string	$object_type	needs to be a valid object type
	 */
	public function hasKioskMode(string $object_type) : bool {
	}
}
