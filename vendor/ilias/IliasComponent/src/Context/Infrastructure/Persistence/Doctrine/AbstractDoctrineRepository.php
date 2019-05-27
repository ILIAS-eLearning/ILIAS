<?php

namespace srag\IliasComponent\Context\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use srag\IliasComponent\Context\Command\Aggregate\Entity;
use srag\IliasComponent\Context\Infrastructure\Persistence\Doctrine\Exception\EntityNotFoundException;
use srag\IliasComponent\Context\Infrastructure\Persistence\Doctrine\Type\DoctrineDomainIdType;
use srag\IliasComponent\Context\Infrastructure\Repository\Doctrine\DoctrineInfrastructureRepository;
use srag\IliasComponent\Context\Infrastructure\Resources\Doctrine\Connection\DoctrineConnectionFactory;
use srag\IliasComponent\Context\Infrastructure\Resources\Doctrine\Connection\DoctrineMySqlConnection;
use srag\IliasComponent\Context\Infrastructure\Resources\Doctrine\DoctrineEntityManager;

/**
 * Class AbstractDoctrineRepository
 *
 * @package srag\IliasComponent\Infrastructure\Persistence\Doctrine
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractDoctrineRepository implements DoctrineInfrastructureRepository {

	/**
	 * @var EntityManager;
	 */
	protected $entity_manager;
	/**
	 * @var string
	 */
	protected $class;


	/**
	 * @inheritdoc
	 */
	public function __construct(DoctrineEntityManager $em) {
		$this->entity_manager = $em->entity_manager;
	}

	//TODO
	public function getConnection() {
		global $DIC; //TODO!!!

		$connection_factory = new DoctrineConnectionFactory();
		return $connection_factory->getConnection(new DoctrineMySqlConnection(
			$DIC->clientIni()->readVariable("db", "host"),
			$DIC->clientIni()->readVariable("db", "user"),
			$DIC->clientIni()->readVariable("db", "pass"),
			$DIC->clientIni()->readVariable("db", "name"),
			'utf-8'));


	}


	/**
	 * @inheritdoc
	 */
	public function doFindById(int $id): ?Entity {
		if (!$id) {
			throw new LogicException("No fields provided.");
		}

		$this->addFieldCriteria($qb = $this->entity_manager->createQueryBuilder(), $fields);
		/*$qb->setFirstResult(0);
		$qb->setMaxResults(1);*/

		//echo $qb->getQuery()->getSQL();exit;

		if (($entity = $qb->getQuery()->getOneOrNullResult()) === null) {
			throw EntityNotFoundException::createForId($this->class, $id);
		}

		return $entity;
	}


	/**
	 * @inheritdoc
	 */
	public function doFindByFields(array $fields): array {
		if (!$fields) {
			throw new LogicException("No fields provided.");
		}

		$this->addFieldCriteria($qb = $this->entity_manager->createQueryBuilder(), $fields);
		/*$qb->setFirstResult(0);
		$qb->setMaxResults(1);*/

		//echo $qb->getQuery()->getSQL();exit;

		if (($entity = $qb->getQuery()->getResult()) === null) {
			throw EntityNotFoundException::createForFields($this->class, $fields);
		}

		return $entity;
	}


	/**
	 * @inheritdoc
	 */
	public function doSave($entity): void {
		// TODO
	}


	/**
	 * @inheritdoc
	 */
	public function doDelete($entity): void {
		// TODO
	}


	/**
	 * @return QueryBuilder
	 */
	protected function createQueryBuilder(): QueryBuilder {
		$qb = $this->entity_manager->createQueryBuilder();
		$qb->select($alias = $this->getAlias());
		$qb->from($this->class, $alias);

		return $qb;
	}


	/**
	 * @param QueryBuilder $qb
	 * @param array        $fields
	 * @param bool         $or
	 */
	protected function addFieldCriteria(QueryBuilder $qb, array $fields, bool $or = false): void {
		if (!$fields) {
			return;
		}
		$expr = $qb->expr();
		$where = $or ? $expr->orX() : $expr->andX();
		$alias = $this->getAlias();
		$associations = $this->entity_manager->getClassMetadata($this->class)->getAssociationMappings();
		foreach ($fields as $field => $value) {
			$fieldAlias = $alias . "." . $field;
			if ($value === null) {
				$where->add($expr->isNull($fieldAlias));
				continue;
			}
			if ($value === true || $value === false) {
				$where->add($expr->eq($fieldAlias, $value ? "TRUE" : "FALSE"));
				continue;
			}
			$param = $this->addFieldParameter($qb, (string)$field, $value);
			if (is_array($value)) {
				$where->add($expr->in($fieldAlias, $param));
			} elseif (isset($associations[$field])) {
				$where->add($expr->eq("IDENTITY(" . $fieldAlias . ")", $param));
			} else {
				$where->add($expr->eq($fieldAlias, $param));
			}
		}
		$qb->andWhere($where);
	}


	/**
	 * @return string
	 */
	protected function getAlias(): string {
		return $this->alias ?? ($this->alias = strtolower((string)preg_replace([ "/([A-Z]+)([A-Z][a-z])/", "/([a-z\d])([A-Z])/" ], [
				"\\1_\\2",
				"\\1_\\2"
			], (string)(($i = strrpos($this->class, "\\")) === false ? $this->class : substr($this->class, $i + 1)))));
	}


	/**
	 * @param QueryBuilder $qb
	 * @param string       $field
	 * @param mixed        $value
	 * @param string|null  $type
	 *
	 * @return string
	 */
	protected function addFieldParameter(QueryBuilder $qb, string $field, $value, string $type = null): string {
		$name = $base = str_replace(".", "_", $field);
		$counter = 0;
		while ($qb->getParameter($name) !== null) {
			$name = $base . ++ $counter;
		}
		$qb->setParameter($name, $value, $type ?? DoctrineDomainIdType::resolveName($value));

		return ":" . $name;
	}
}
