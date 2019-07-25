<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ilDateTime;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\RevisionIdContract;

/**
 * Class RevisionId
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 */
class RevisionId implements RevisionIdContract
{
	/**
	 * @var string
	 */
	protected $uuid;
	
	/**
	 * @var ilDateTime
	 */
	protected $createdOn;
	
	public function __construct(string $revisionId, ilDateTime $createdOn)
	{
		$this->uuid = $revisionId;
		$this->createdOn = $createdOn;
	}
	
	public function getRevisionId(): string
	{
		return $this->uuid;
	}
	
	public function getCreatedOn(): ilDateTime
	{
		return $this->createdOn;
	}
}
