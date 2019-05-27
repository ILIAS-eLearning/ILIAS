<?php

namespace srag\IliasComponentCourse\Course\Member\Infrastructure\Repository\Doctrine;

use srag\IliasComponent\Context\DomainRepository;
use srag\IliasComponent\Context\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use srag\IliasComponent\Context\Infrastructure\Resources\Doctrine\DoctrineEntityManager;

use srag\IliasComponentCourse\Course\Query\Dto\FormDto;
use srag\IliasComponentCourse\Course\Query\Projection\CourseMemberReadOnlyRepository;
use srag\IliasComponentCourse\Course\Command\Command;

/**
 * Class MemberEntityRepository
 *
 * @package srag\IliasComponentCourse\Course\Member\Infrastructure\Repository\Doctrine
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class MemberEntityRepository extends AbstractDoctrineRepository {

	/**
	 * @inheritdoc
	 */
	public function __construct(DoctrineEntityManager $em) {
		$doc = $em->visit($this->getRepositoryXmlMetaDataConfiguration(),$em);

		$this->class = CourseMember::class;

		parent::__construct($doc);
	}


	/**
	 * @return DomainRepository
	 */
	public function getReadOnlyRepository(): DomainRepository {
		return new MemberReadOnlyRepository($this);
	}


	/**
	 * @return DomainRepository
	 */
	public function getWriteOnlyRepository(): DomainRepository {
		return new MemberWriteOnlyRepository($this);
	}


	/**
	 * @return array
	 */
	public function getRepositoryXmlMetaDataConfiguration(): array {
		return [
			__DIR__ . "/../../../../Course/Infrastructure/Resources/Doctrine/Entity" => 'srag\IliasComponentCourse\Course\Course\Query\Entity\Course',
			__DIR__ . "/../../../../Course/Infrastructure/Resources/Doctrine/Entity" => 'srag\IliasComponentCourse\Course\Course\Query\Entity\CourseMember',
			__DIR__ . "/../../../../Member/Infrastructure/Resources/Doctrine/Entity" => 'srag\IliasComponentCourse\Course\Course\Query\Entity\Member',
			__DIR__ . "/../../../../../../iliascomponentuser/src/User/Infrastructure/Resources/Doctrine/Entity",
			__DIR__ . "/../../Resources/Doctrine/Entity"
		];
	}
}
