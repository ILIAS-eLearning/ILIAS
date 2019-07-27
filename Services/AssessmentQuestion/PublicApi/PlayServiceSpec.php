<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\PlayServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\RevisionIdContract;
use ILIAS\UI\Component\Link\Link;

/**
 * Class PlayServiceSpec
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 */
class PlayServiceSpec implements PlayServiceSpecContract {

	/**
	 * @var int
	 */
	protected $containerId;
	
	/**
	 * @var int
	 */
	protected $actorId;
	
	/**
	 * @var array
	 */
	protected $additionalButtons;
	
	/**
	 * PlayServiceSpec constructor.
	 *
	 * @param int $containerId
	 * @param int $actorId
	 */
	public function __construct(int $containerId, int $actorId)
	{
		$this->containerId = $containerId;
		$this->actorId = $actorId;
		$this->additionalButtons = [];
	}

	/**
	 * @return void
	 */
	public function withAdditionalButton() {
		// TODO: Implement withAdditionalButton() method.
	}
}