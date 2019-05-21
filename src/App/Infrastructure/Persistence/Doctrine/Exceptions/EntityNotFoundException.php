<?php

namespace ILIAS\App\Infrastructure\Persistence\Doctrine\Exceptions;

require_once('Services/Exceptions/classes/class.ilException.php');

/**
 * Class EntityNotFoundException
 *
 */
class EntityNotFoundException extends Exception {
	/**
	 * @param mixed $id
	 */
	public static function createForId(string $class, $id): self
	{
		return new self('Entity "'.$class.'" with identity '.json_encode($id).' cannot be found.');
	}
	public static function createForFields(string $class, array $fields): self
	{
		return new self('Entity "'.$class.'" with fields matching '.json_encode($fields).' cannot be found.');
	}
}
