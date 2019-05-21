<?php
namespace ILIAS\App\CoreApp\Member\Infrastructure\Repository\Doctrine;

use ILIAS\App\CoreApp\Member\Domain\Repository\MemberReadonlyRepository;
use ILIAS\App\CoreApp\Member\Domain\Repository\MemberWriteonlyRepository;
use ILIAS\App\Infrasctrutre\Persistence\Doctrine\AbstractDoctrineRepository;
use ILIAS\App\Infrasctrutre\Persistence\Doctrine\DoctrineEntityManager;

class MemberEntityRepository extends AbstractDoctrineRepository {

	protected $readonly_repository;
	protected $writeonly_repository;

	/**
	 * CourseMemberEntityRepository constructor.
	 *
	 * @param DoctrineEntityManager $em
	 */
	public function __construct($em)
	{
		/**
		 * @var DoctrineEntityManager
		 */
		$doc = $em->visit($this->getRepositoryXmlMetaDataConfiguration());

		parent::__construct($doc->getEntityManager(), 'ILIAS\App\CoreApp\Course\Domain\Entity\CourseMember');
	}


	/**
	 * @return mixed
	 */
	public function getReadonlyRepository() {
		return new MemberReadonlyRepository($this);
	}


	/**
	 * @return mixed
	 */
	public function getWriteonlyRepository() {
		return new MemberWriteonlyRepository($this);
	}


	//TODO
	/**
	 * @return array
	 */
	public function getRepositoryXmlMetaDataConfiguration():array {
		return ['/var/www/ilias/src/App/CoreApp/Course/Infrastructure/Resources/Config/Doctrine/Entity',
			'/var/www/ilias/src/App/CoreApp/User/Infrastructure/Resources/Doctrine/Entity',
			'/var/www/ilias/src/App/CoreApp/Member/Infrastructure/Resources/Config/Doctrine/Entity'];
	}

}