<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;

/**
 * Class ClozeGapItem
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ClozeGapItem extends AbstractValueObject {
    /**
     * @var string
     */
    protected $text;
    
    /**
     * @var int
     */
    protected $score;
    
    /**
     * @param string $text
     * @param int $score
     * @return ClozeGapItem
     */
    public static function create(string $text, int $score) : ClozeGapItem {
        $item = new ClozeGapItem();
        $item->text = $text;
        $item->score = $score;
        return $item;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return number
     */
    public function getScore()
    {
        return $this->score;
    }

    public function equals(AbstractValueObject $other): bool
    {
        /** @var ClozeGapItem $other */
        return get_class($this) === get_class($other) &&
               $this->text === $other->text &&
               $this->score === $other->score;
    }
}