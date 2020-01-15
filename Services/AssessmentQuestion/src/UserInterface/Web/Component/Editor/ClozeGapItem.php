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
    const VAR_TEXT = 'cgi_text';
    const VAR_POINTS = 'cgi_points';
    
    /**
     * @var string
     */
    protected $text;
    
    /**
     * @var int
     */
    protected $points;
    
    /**
     * @param string $text
     * @param int $points
     * @return ClozeGapItem
     */
    public static function create(string $text, int $points) : ClozeGapItem {
        $item = new ClozeGapItem();
        $item->text = $text;
        $item->points = $points;
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
    public function getPoints()
    {
        return $this->points;
    }

    public function getAsArray() : array {
        return [
            self::VAR_TEXT => $this->text,
            self::VAR_POINTS => $this->points
        ];
    }
    
    public function equals(AbstractValueObject $other): bool
    {
        /** @var ClozeGapItem $other */
        return get_class($this) === get_class($other) &&
               $this->text === $other->text &&
               $this->points === $other->points;
    }
}