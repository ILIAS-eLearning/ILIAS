<?php
namespace ILIAS\App\Infrasctrutre\Persistence\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use ILIAS\App\Domain\DomainId;
use ILIAS\App\Infrastructure\Persistence\Doctrine\Type\DoctrineDomainIdType;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\Common\Persistence\Mapping\MappingException;

use ILIAS\Infrastructure\Repository\Doctrine\Exceptions\EntityNotFoundException;

class AbstractDoctrineRepository {


	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var string
	 */
	protected $class;

	/**
	 * @psalm-param class-string $class
	 */
	public function __construct(EntityManagerInterface $em,string $class)
	{
		$this->em = $em;
		$this->class = $class;
	}

	function doFind(array $fields) {
		if (!$fields) {
			throw new \LogicException('No fields provided.');
		}

		$this->addFieldCriteria($qb = $this->createQueryBuilder(), $fields);
		/*$qb->setFirstResult(0);
		$qb->setMaxResults(1);*/

		//echo $qb->getQuery()->getSQL();exit;

		if (null === $entity = $qb->getQuery()->getOneOrNullResult()) {
			throw EntityNotFoundException::createForFields($this->class, $fields);
		}

		return $entity;
	}


	function doFindByFields(array $fields): array {
		if (!$fields) {
			throw new \LogicException('No fields provided.');
		}

		$this->addFieldCriteria($qb = $this->createQueryBuilder(), $fields);
		/*$qb->setFirstResult(0);
		$qb->setMaxResults(1);*/

		//echo $qb->getQuery()->getSQL();exit;

		if (null === $entity = $qb->getQuery()->getResult()) {
			throw EntityNotFoundException::createForFields($this->class, $fields);
		}

		return $entity;
	}

	private function createQueryBuilder(): QueryBuilder
	{
		$qb = $this->em->createQueryBuilder();
		$qb->select($alias = $this->getAlias());
		$qb->from($this->class, $alias);
		return $qb;
	}

	private function addFieldCriteria(QueryBuilder $qb, array $fields, bool $or = false): void
	{
		if (!$fields) {
			return;
		}
		$expr = $qb->expr();
		$where = $or ? $expr->orX() : $expr->andX();
		$alias = $this->getAlias();
		$associations = $this->em->getClassMetadata($this->class)->getAssociationMappings();
		foreach ($fields as $field => $value) {
			$fieldAlias = $alias.'.'.$field;
			if (null === $value) {
				$where->add($expr->isNull($fieldAlias));
				continue;
			}
			if (true === $value || false === $value) {
				$where->add($expr->eq($fieldAlias, $value ? 'TRUE' : 'FALSE'));
				continue;
			}
			$param = $this->addFieldParameter($qb, (string) $field, $value);
			if (\is_array($value)) {
				$where->add($expr->in($fieldAlias, $param));
			} elseif (isset($associations[$field])) {
				$where->add($expr->eq('IDENTITY('.$fieldAlias.')', $param));
			} else {
				$where->add($expr->eq($fieldAlias, $param));
			}
		}
		$qb->andWhere($where);
	}


	function doSave($entity): void {
		// TODO: Implement doSave() method.
	}

	function doDelete($entity): void {
		// TODO: Implement doDelete() method.
	}


	private function getAlias(): string
	{
		return $this->alias ?? ($this->alias = strtolower((string) preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], ['\\1_\\2', '\\1_\\2'], (string) (false === ($i = strrpos($this->class, '\\')) ? $this->class : substr($this->class, $i + 1)))));
	}

	/**
	 * @param mixed $value
	 */
	private function addFieldParameter(QueryBuilder $qb, string $field, $value, string $type = null): string
	{

		$name = $base = str_replace('.', '_', $field);
		$counter = 0;
		while (null !== $qb->getParameter($name)) {
			$name = $base.++$counter;
		}
		$qb->setParameter($name, $value, $type ?? DoctrineDomainIdType::resolveName($value));
		return ':'.$name;
	}
}
