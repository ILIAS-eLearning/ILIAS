<?php


namespace ILIAS\Changelog\Interfaces;


/**
 * Interface Event
 * @package ILIAS\Changelog\Interfaces
 */
interface Event {

	/**
	 * @return int
	 */
	public function getTypeId(): int;
}