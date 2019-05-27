<?php

namespace srag\IliasComponent\Context\Query;

use srag\IliasComponent\Context\DomainRepository;
use srag\IliasComponent\Context\Query\Dto\FormDto;
use srag\IliasComponent\Context\Query\Dto\RowDto;

/**
 * Interface ContextReadOnlyRepository
 *
 * @package srag\IliasComponent\Query
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface ContextReadOnlyRepository extends DomainRepository {

	/**
	 * @param int $id
	 *
	 * @return FormDto|null
	 */
	public function get(int $id): FormDto;


	/**
	 *
	 * @return RowDto[]|null
	 */
	public function all(): array;
}
