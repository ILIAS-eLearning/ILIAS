<?php

namespace srag\IliasComponentCourse\Course\Infrastructure\Repository\Doctrine;

use srag\IliasComponent\Context\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use srag\IliasComponent\Context\Infrastructure\Resources\Doctrine\DoctrineEntityManager;
use srag\IliasComponent\Context\Infrastructure\Resources\Doctrine\Entity\DoctrineEntityManager as DoctrineEntityManagerInterface;
use srag\IliasComponent\Context\Course\Domain\Entity\Course;

/**
 * Class CourseEntityRepository
 *
 * @package srag\IliasComponentCourse\Course\Course\Infrastructure\Repository\Doctrine
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class CourseEntityRepository extends AbstractDoctrineRepository {

	/**
	 * @inheritdoc
	 */
	public function __construct(DoctrineEntityManager $em) {
		$doc = $em->visit($this->getRepositoryXmlMetaDataConfiguration());

		$this->class = Course::class;

		parent::__construct($doc);
	}


	/**
	 * @return array
	 */
	public function getRepositoryXmlMetaDataConfiguration(): array {
		return [
			__DIR__ . "/../../Resources/Doctrine/Entity",
			__DIR__ . "/../../../../../iliascomponentuser/src/User/Infrastruktur/Resources/Doctrine/Entity"
		];
	}
}
