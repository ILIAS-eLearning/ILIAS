<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Value;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Value\Format\AnswerOptionValueInFormat;

interface AnswerOptionValue {

	public function getAnswerValue(): AnswerOptionValueInFormat;
}