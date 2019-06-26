<?php

namespace ILIAS\Data\Domain;

use Ramsey\Uuid\Uuid;

class Guid {
	public static function createGuid()
	{
		return Uuid::uuid4()->toString();
	}
}