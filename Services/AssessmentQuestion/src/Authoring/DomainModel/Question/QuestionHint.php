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
	private $label_hint;
	/**
	 * @var float
	 */
	private $points;


	/**
	 * QuestionHint constructor.
	 *
	 * @param string $label_hint
	 * @param float  $points
	 */
	public function __construct(string $label_hint, float $points) {
		$this->label_hint = $label_hint;
		$this->points = $points;
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
    
    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject::jsonSerialize()
     */
    public function jsonSerialize() {
        return get_object_vars($this);
    }
}