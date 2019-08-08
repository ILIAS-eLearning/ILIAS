<?php

namespace ILIAS\AssessmentQuestion\Application;

/**
 * Class AuthoringApplicationServiceSpec
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AuthoringApplicationServiceSpec {

	/**
	 * @var int
	 */
	protected $initiating_user_id;


    /**
     * AuthoringApplicationServiceSpec constructor.
     *
     * @param int $initiating_user_id
     */
	public function __construct(
		int $initiating_user_id) {
		$this->initiating_user_id = $initiating_user_id;
	}


	/**
	 * @return int
	 */
	public function getInitiatingUserId(): int {
		return $this->initiating_user_id;
	}
}