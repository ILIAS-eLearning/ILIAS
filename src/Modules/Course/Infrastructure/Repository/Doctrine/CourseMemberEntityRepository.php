<?php
namespace ILIAS\Modules\Course\Infrastructure\Repository\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use ILIAS\Infrasctrutre\Persistence\Doctrine\AbstractDoctrineRepository;

class CourseMemberEntityRepository extends AbstractDoctrineRepository {

	/**
	 * @psalm-param class-string $class
	 */
	public function __construct($em)
	{
		//$em->getFilters()->enable("coursemember");
		parent::__construct($em, 'ILIAS\Modules\Course\Domain\Entity\CourseMember');
	}

}