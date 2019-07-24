<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;
s
/**
 * Class AssessmentServices
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package     Services/AssessmentQuestion
 */
class Assessment
{
	/**
	 * @return Control
	 */
	public function control() : Control
	{
		return new Control();
	}
	
	/**
	 * @return Service
	 */
	public function service() : Service
	{
		return new Service();
	}
	
	/**
	 * @return Specification
	 */
	public function specification() : Specification
	{
		return new Specification();
	}
}
