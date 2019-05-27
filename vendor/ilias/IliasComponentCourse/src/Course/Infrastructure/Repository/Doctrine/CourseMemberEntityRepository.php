<?php

namespace srag\IliasComponentCourse\Course\Infrastructure\Repository\Doctrine;

use srag\IliasComponent\Context\DomainRepository;
use srag\IliasComponent\Context\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use srag\IliasComponent\Context\Infrastructure\Resources\Doctrine\DoctrineEntityManager;
use srag\IliasComponentCourse\Course\Query\Dto\FormDto;


/**
 * Class CourseMemberEntityRepository
 *
 * @package srag\IliasComponentCourse\Course\Course\Infrastructure\Repository\Doctrine
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class CourseMemberEntityRepository extends AbstractDoctrineRepository {

	/**
	 * @inheritdoc
	 */
	public function __construct(DoctrineEntityManager $em) {
		$doc = $em->visit($this->getRepositoryXmlMetaDataConfiguration(),$this->getConnection());

		$this->class = FormDto::class;

		parent::__construct($doc);
	}


	/**
	 * @return array
	 */
	public function getRepositoryXmlMetaDataConfiguration(): array {
		return [
			__DIR__ . "/../../Resources/Doctrine/Entity",
			__DIR__ . "/../../../../../../iliascomponentuser/src/User/Infrastructure/Resources/Doctrine/Entity"
		];
	}


	/**
	 * @return DomainRepository
	 */
	public function getReadOnlyRepository(): DomainRepository {
		return new CourseMemberReadOnlyRepository($this);
	}
}
