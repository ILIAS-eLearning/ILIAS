<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;

/**
 * Class ClozeGapConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ClozeGapConfiguration extends AbstractValueObject {
    const TYPE_TEXT = 'clz_text';
    const TYPE_NUMBER = 'clz_number';
    const TYPE_DROPDOWN = 'clz_dropdown';
    
    /**
     * @var string
     */
    protected $type;
    
    /**
     * @var ClozeGapItem[]
     */
    protected $items;
    
    /**
     * @param string $type
     * @param array $items
     * @return ClozeGapConfiguration
     */
    public static function create(string $type, array $items) : ClozeGapConfiguration {
        $config = new ClozeGapConfiguration();
        $config->type = $type;
        $config->items = $items;
        return $config;
    }
    
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return ClozeGapItem[] 
     */
    public function getItems()
    {
        return $this->items;
    }
    
    /**
     * @return array
     */
    public function getItemsArray(): array {
        $var_array = [];
        
        foreach($this->items as $variable) {
            $var_array[] = $variable->getAsArray();
        }
        
        return $var_array;
    }

    public function equals(AbstractValueObject $other): bool
    {
        /** @var ClozeGapConfiguration $other */
        return get_class($this) === get_class($other) &&
        $this->type === $other->type &&
        $this->itemsEquals($other->items);
    }
    
    /**
     * @param array $items
     * @return bool
     */
    private function itemsEquals(array $items) : bool
    {
        if (count($this->items) !== count($items)) {
            return false;
        }
        
        for ($i = 0; $i < count($items); $i += 1) {
            if(!$this->items[$i]->equals($items[$i])) {
                return false;
            }
        }
        
        return true;
    }
}