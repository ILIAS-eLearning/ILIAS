<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi;

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
class QuestionId implements QuestionIdContract
{
	/**
	 * @var string
	 */
	protected $uuid;
	
	public function __construct(string $questionUuid = '')
	{
		if( !strlen($questionUuid) )
		{
			$questionUuid = $this->buildUuid();
		}
		else
		{
			$this->validateUuid();
		}
		
		$this->uuid = $questionUuid;
	}
	
	/**
	 * @return string
	 */
	protected function buildUuid()
	{
		return 'xyz-i-am-a-valid-uuidV4';
	}
	
	/**
	 *
	 */
	protected function validateUuid()
	{
		if( false ) // when invalid
		{
			// throw exeption
		}
	}
	
	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->uuid;
	}
}
