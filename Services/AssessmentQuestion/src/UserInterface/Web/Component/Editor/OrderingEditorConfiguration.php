<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class OrderingEditorConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class OrderingEditorConfiguration extends AbstractConfiguration
{
    /**
     * @var bool
     */
    protected $vertical;
    /**
     * @var int
     */
    protected $minimum_size;
    /**
     * @var int
     */
    protected $geometry;
    
    public static function create(
        bool $vertical, 
        int $minimum_size, 
        int $geometry) : OrderingEditorConfiguration
    {
        $object = new OrderingEditorConfiguration();
        $object->vertical = $vertical;
        $object->minimum_size = $minimum_size;
        $object->geometry = $geometry;
        return $object;
    }
    
    /**
     * @return boolean
     */
    public function isVertical()
    {
        return $this->vertical;
    }

    /**
     * @return int
     */
    public function getMinimumSize()
    {
        return $this->minimum_size;
    }

    /**
     * @return int
     */
    public function getGeometry()
    {
        return $this->geometry;
    }

    public function equals(AbstractValueObject $other): bool
    {
        /** @var OrderingEditorConfiguration $other */
        return get_class($this) === get_class($other) &&
               $this->isVertical() === $other->isVertical() &&
               $this->getMinimumSize() === $other->getMinimumSize() &&
               $this->getGeometry() === $other->getGeometry();
    }
}