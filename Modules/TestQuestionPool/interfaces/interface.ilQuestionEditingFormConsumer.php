<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

interface ilQuestionEditingFormConsumer
{
	/**
	 * @return string
	 */
	public function getQuestionEditingFormBackTargetLabel();

	/**
	 * @param $context
	 * @return string
	 */
	public function getQuestionEditingFormBackTarget($context);
} 