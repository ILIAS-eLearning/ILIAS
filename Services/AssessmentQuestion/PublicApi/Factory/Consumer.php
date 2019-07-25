<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;


use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionResourcesCollector;

/**
 * Class Consumer
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class Consumer
{
	/**
	 * @return QuestionResourcesCollector
	 */
	public function questionRessourcesCollector(): QuestionResourcesCollector
	{
		return new QuestionResourcesCollector();
	}
}
