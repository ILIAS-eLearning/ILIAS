<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question;

use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject;

/**
 * Class QuestionHint
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class QuestionHint extends AbstractValueObject{

	/**
	 * @var string
	 */
	protected $label_hint;
	/**
	 * @var float
	 */
	protected $points;


	/**
	 * @param string $label_hint
	 * @param float  $points
	 *
	 * @return QuestionHint
	 */
	static function create(string $label_hint, float $points) : QuestionHint{
		$object = new QuestionHint();
		$object->label_hint = $label_hint;
		$object->points = $points;
		return $object;
	}


	/**
	 * @return string
	 */
	public function getLabelHint(): string {
		return $this->label_hint;
	}


	/**
	 * @return float
	 */
	public function getPoints(): float {
		return $this->points;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject::equals()
	 */
    public function equals(AbstractValueObject $other): bool
    {
        return $this->getLabelHint() === $other->getLabelHint() &&
               $this->getPoints() === $other->points;
    }
}