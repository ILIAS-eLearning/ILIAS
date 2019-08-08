<?php

namespace ILIAS\AssessmentQuestion\DomainModel;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;

/**
 * Class QuestionHint
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
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
     * @param AbstractValueObject $other
     *
     * @return bool
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var QuestionHint $other */
        return $this->getLabelHint() === $other->getLabelHint() &&
               $this->getPoints() === $other->points;
    }
}