<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class KprimChoiceEditorConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class KprimChoiceEditorConfiguration extends AbstractConfiguration {
    /**
     * @var bool
     */
    protected $shuffle_answers;
    /**
     * @var bool
     */
    protected $single_line;
    /**
     * @var int
     */
    protected $thumbnail_size;
    /**
     * @var string
     */
    protected $label_true;
    /**
     * @var string
     */
    protected $label_false;
    /**
     * @var int
     */
    protected $points;
    /**
     * @var int
     */
    protected $halve_points_at;

    static function create(bool $shuffle_answers = false,
                           bool $single_line = true,
                           int $thumbnail_size = 0,
                           string $label_true = "",
                           string $label_false = "",
                           int $points, 
                           int $half_points_at) : KprimChoiceEditorConfiguration
        {
            $object = new KprimChoiceEditorConfiguration();
            $object->single_line = $single_line;
            $object->shuffle_answers = $shuffle_answers;
            $object->thumbnail_size = $thumbnail_size;
            $object->label_true = $label_true;
            $object->label_false = $label_false;
            $object->points = $points;
            $object->halve_points_at = $half_points_at;
            
            return $object;
    }
    
    /**
     * @return boolean
     */
    public function isShuffle_answers()
    {
        return $this->shuffle_answers;
    }

    /**
     * @return boolean
     */
    public function isSingle_line()
    {
        return $this->single_line;
    }

    /**
     * @return number
     */
    public function getThumbnail_size()
    {
        return $this->thumbnail_size;
    }

    /**
     * @return string
     */
    public function getLabel_true()
    {
        return $this->label_true;
    }

    /**
     * @return string
     */
    public function getLabel_false()
    {
        return $this->label_false;
    }

    /**
     * @return number
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @return number
     */
    public function getHalve_points_at()
    {
        return $this->halve_points_at;
    }

    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject::equals()
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var KprimChoiceEditorConfiguration $other */
        return $this->halve_points_at === $other->halve_points_at &&
               $this->label_false === $other->label_false &&
               $this->label_true === $other->label_true &&
               $this->points === $other->points &&
               $this->shuffle_answers === $other->shuffle_answers &&
               $this->single_line === $other->single_line &&
               $this->thumbnail_size === $other->thumbnail_size;
    }
}