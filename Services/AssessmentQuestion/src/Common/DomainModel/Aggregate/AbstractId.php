<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate;

/**
 * Class AbstractId
 *
 * @package ILIAS\AssessmentQuestion\Common\Model
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AbstractId implements DomainObjectId {

	/**
	 * @var string
	 */
	private $id;


	/**
	 * AbstractAggregateId constructor.
	 *
	 * @param null $id
	 */
	public function __construct($id = null) {
		$this->id = $id ?: Guid::create();
	}


	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}


	/**
	 * @param AggregateId $id
	 *
	 * @return bool
	 */
	public function equals(AggregateId $id): bool {
		return $this->id === $id->id();
	}
}