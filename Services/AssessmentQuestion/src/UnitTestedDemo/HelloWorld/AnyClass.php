<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\UnitTestedDemo\HelloWorld;

/**
 * Class HelloWorld
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class AnyClass
{
	/**
	 * @var string
	 */
	protected $anyMember = 'Hello World!';
	
	/**
	 * @return string
	 */
	public function getAnyMember(): string
	{
		return $this->anyMember;
	}
	
	/**
	 * @param string $anyMember
	 */
	public function setAnyMember(string $anyMember): void
	{
		$this->anyMember = $anyMember;
	}
}
