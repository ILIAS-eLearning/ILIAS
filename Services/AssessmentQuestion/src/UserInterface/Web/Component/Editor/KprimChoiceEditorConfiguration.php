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

    static function create(bool $shuffle_answers = false,
                           bool $single_line = true,
                           int $thumbnail_size = 0,
                           ?string $label_true = "",
                           ?string $label_false = "") : KprimChoiceEditorConfiguration
        {
            $object = new KprimChoiceEditorConfiguration();
            $object->single_line = $single_line;
            $object->shuffle_answers = $shuffle_answers;
            $object->thumbnail_size = $thumbnail_size;
            $object->label_true = $label_true;
            $object->label_false = $label_false;
            
            return $object;
    }
    
    /**
     * @return boolean
     */
    public function isShuffleAnswers()
    {
        return $this->shuffle_answers;
    }

    /**
     * @return boolean
     */
    public function isSingleLine()
    {
        return $this->single_line;
    }

    /**
     * @return number
     */
    public function getThumbnailSize()
    {
        return $this->thumbnail_size;
    }

    /**
     * @return string
     */
    public function getLabelTrue()
    {
        return $this->label_true;
    }

    /**
     * @return string
     */
    public function getLabelFalse()
    {
        return $this->label_false;
    }

    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject::equals()
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var KprimChoiceEditorConfiguration $other */
        return $this->label_false === $other->label_false &&
               $this->label_true === $other->label_true &&
               $this->shuffle_answers === $other->shuffle_answers &&
               $this->single_line === $other->single_line &&
               $this->thumbnail_size === $other->thumbnail_size;
    }
}