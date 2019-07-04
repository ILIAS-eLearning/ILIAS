<?php
/* Copyright (c) 2019 Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate;

/**
 * Class QuestionId
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
interface DomainObjectId {

	public function getId(): string;


	public function equals(DomainObjectId $anId): bool;
}