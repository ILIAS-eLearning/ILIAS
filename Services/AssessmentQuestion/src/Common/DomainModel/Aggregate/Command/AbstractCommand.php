<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */

namespace ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command;

abstract class AbstractCommand implements CommandContract {
	/**
	 * @var int
	 */
	protected $issuing_user_id;

	public function __construct(int $issuing_user_id) {
		$this->issuing_user_id = $issuing_user_id;
	}
}