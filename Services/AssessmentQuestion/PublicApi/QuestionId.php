<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;

/**
 * Class QuestionId
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class QuestionId implements QuestionIdContract
{
	/**
	 * @var string
	 */
	protected $uuid;
	
	/**
	 * @var string
	 */
	protected $iliasNicId;
	
	public function __construct(string $questionUuid, string $iliasNicId)
	{
		$this->uuid = $questionUuid;
		$this->iliasNicId = $iliasNicId;
	}
	
	public function getId(): string
	{
		return $this->uuid;
	}
	
	public function getIliasNicId(): string
	{
		// TODO: Implement getIliasNicId() method.
	}
	
}
