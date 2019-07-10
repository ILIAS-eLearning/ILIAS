<?php


namespace ILIAS\Changelog\Interfaces;


interface Event {

	/**
	 * @return int
	 */
	public function getTypeId(): int;
}