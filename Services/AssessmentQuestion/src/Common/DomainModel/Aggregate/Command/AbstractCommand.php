<?php
/* Copyright (c) 2019 - Martin Studer <ms@studer-raimann.ch> - Extended GPL, see LICENSE */

namespace ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Command;

abstract class AbstractCommand implements CommandContract {
	/**
	 * @var int
	 */
	protected $issuing_user_id;
}