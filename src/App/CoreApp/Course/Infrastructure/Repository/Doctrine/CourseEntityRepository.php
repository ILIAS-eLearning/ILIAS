<?php
namespace ILIAS\App\CoreApp\Course\Infrastructure\Repository\Doctrine;

use ILIAS\App\Infrasctrutre\Persistence\Doctrine\AbstractDoctrineRepository;
use ILIAS\App\Infrasctrutre\Persistence\Doctrine\DoctrineEntityManager;

class CourseEntityRepository extends AbstractDoctrineRepository {

	/**
	 * @psalm-param class-string $class
	 */
	public function __construct($em)
	{
		/**
		 * @var DoctrineEntityManager
		 */
		$doc = $em->visit($this->getRepositoryXmlMetaDataConfiguration());

		parent::__construct($doc->getEntityManager(), 'ILIAS\App\CoreApp\Course\Domain\Entity\Course');
	}

	//TODO
	/**
	 * @return array
	 */
	public function getRepositoryXmlMetaDataConfiguration():array {
		return ['/var/www/ilias/src/App/CoreApp/Course/Infrastructure/Resources/Config/Doctrine/Entity',
			'/var/www/ilias/src/App/CoreApp/User/Infrastructure/Resources/Doctrine/Entity'];
	}

}