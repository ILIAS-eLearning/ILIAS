<?php

namespace srag\IliasComponent\Context\Infrastructure\Persistence\Doctrine\Type;

/**
 * Class IlObjectId
 *
 * @package srag\IliasComponent\Infrastructure\Persistence\Doctrine\Type
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
final class IlObjectId extends DoctrineDomainIdType {

	const NAME = "obj_id";


	/**
	 * @inheritdoc
	 */
	public function getName(): string {
		return self::NAME;
	}
}
