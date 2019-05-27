<?php

namespace srag\IliasComponent\Context\Infrastructure\Persistence\Doctrine\Exception;

/**
 * Class EntityNotFoundException
 *
 * @package srag\IliasComponent\Infrastructure\Persistence\Doctrine\Exception
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class EntityNotFoundException extends Exception {

	/**
	 * @param string $class
	 * @param int    $id
	 *
	 * @return self
	 */
	public static function createForId(string $class, int $id): self {
		return new self("Entity " . $class . " with identity " . json_encode($id) . " cannot be found.");
	}


	/**
	 * @param string $class
	 * @param array  $fields
	 *
	 * @return self
	 */
	public static function createForFields(string $class, array $fields): self {
		return new self("Entity " . $class . " with fields matching " . json_encode($fields) . " cannot be found.");
	}
}
