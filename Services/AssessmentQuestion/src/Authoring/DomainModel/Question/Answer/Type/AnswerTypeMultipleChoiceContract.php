<?php
namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Type;

class AnswerTypeMultipleChoice  {

	const TYPE_ID = 'multiple_choice';

	public function construct() {

	}

	public function getId() {
		return self::TYPE_ID;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public static function getName():string {
		$reflection_class = new \ReflectionClass(static::class);
		return $reflection_class->getShortName();
	}
}