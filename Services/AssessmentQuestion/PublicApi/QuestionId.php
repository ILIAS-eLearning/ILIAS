<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ilDateTime;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;

/**
 * Class QuestionId
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 */
class QuestionId implements QuestionIdContract {

	/**
	 * @var string
	 */
	protected $uuid;
	/**
	 * @var string
	 */
	protected $created_on_ilias_nic_id;
	/**
	 * @var ilDateTime
	 */
	protected $created_on;


	public function __construct(string $questionUuid,string $created_on_ilias_nic_id, ilDateTime $created_on) {
		$this->uuid = $questionUuid;
		$this->created_on_ilias_nic_id = $created_on_ilias_nic_id;
		$this->created_on = $created_on;
	}

	/**
	 * @return string
	 */
	public function getUuid(): string {
		return $this->uuid;
	}


	public function getCreatedOnIliasNicId(): string {
		return $this->created_on_ilias_nic_id;
	}


	public function getCreatedOn(): ilDateTime {
		return $this->created_on;
	}
}
