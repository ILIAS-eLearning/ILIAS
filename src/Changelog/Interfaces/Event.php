<?php


namespace ILIAS\Changelog\Interfaces;


/**
 * Interface Event
 * @package ILIAS\Changelog\Interfaces
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
interface Event {

	/**
	 * @return int
	 */
	public function getTypeId(): int;
}