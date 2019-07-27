<?php

namespace ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate;

/**
 * Class AbstractDomainObjectId
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class DomainObjectId {
	/**
	 * @var string
	 */
	private $id;


	public function __construct(string $id = null)
	{
		$this->id = $id ?: Guid::create();
	}

	public function getId(): string {
		return $this->id;
	}


	public function equals(DomainObjectId $anId) : bool{
		return $this->getId() === $anId->getId();
	}
}