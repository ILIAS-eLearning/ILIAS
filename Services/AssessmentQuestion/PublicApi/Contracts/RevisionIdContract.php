<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ilDateTime;

/**
 * Interface RevisionIdContract
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Contracts
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface RevisionIdContract {

	/**
	 * @return string
	 */
	public function getRevisionId():string;

	/**
	 * @return ilDateTime
	 */
	public function getCreatedOn():ilDateTime;

	/**
	 * @return string
	 */
	public function getIliasNicId():string;


}