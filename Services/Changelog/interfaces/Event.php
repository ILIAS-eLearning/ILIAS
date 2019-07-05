<?php


namespace ILIAS\Changelog;


interface Event {

	/**
	 * @return int
	 */
	public function getTypeId(): int;
}