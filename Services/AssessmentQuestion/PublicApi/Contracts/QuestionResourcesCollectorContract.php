<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;


interface QuestionResourcesCollectorContract
{
	/**
	 * @return array
	 */
	public function getMobs(): array;
	
	/**
	 * @return array
	 */
	public function getMediaFiles(): array;
	
	/**
	 * @return array
	 */
	public function getJsFiles(): array;
}