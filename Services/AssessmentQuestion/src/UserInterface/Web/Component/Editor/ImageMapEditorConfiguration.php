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
class ImageMapEditorConfiguration extends AbstractConfiguration {

    /**
     * @var string
     */
    protected $image;
    
    /**
     * @var bool
     */
    protected $multiple_choice;
    
    /**
     * @param string $image
     * @return ImageMapEditorConfiguration
     */
    static function create(string $image, bool $multiple_choice) : ImageMapEditorConfiguration {
        $object = new ImageMapEditorConfiguration();
        $object->image = $image;
        $object->multiple_choice = $multiple_choice;
        return $object;
    }
    
    /**
     * @return string
     */
    public function getImage() {
        return $this->image;
    }
    
    public function isMultipleChoice() {
        return $this->multiple_choice;
    }
    
    /**
     * {@inheritDoc}
     * @see \ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject::equals()
     */
    public function equals(AbstractValueObject $other): bool
    {
        /** @var ImageMapEditorConfiguration $other */
        return get_class($this) === get_class($other) &&
               $this->image === $other->image &&
               $this->multiple_choice === $other->multiple_choice;
    }
}