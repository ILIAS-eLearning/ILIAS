<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ilDateTime;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\UserAnswerIdContract;

/**
 * Class UserAnswerId
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 */
class UserAnswerId implements UserAnswerIdContract {

	/**
	 * @var string
	 */
	protected $uuid;
	/**
	 * @var ilDateTime
	 */
	protected $created_on;


	public function __construct($user_answer_uuid, ilDateTime $created_on) {
		$this->uuid = $user_answer_uuid;
		$this->created_on = $created_on;
	}

	/**
	 * @return string
	 */
	public function getUuid(): string {
		return $this->uuid;
	}

	public function getCreatedOn(): ilDateTime {
		return $this->created_on;
	}
}
