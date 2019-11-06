<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\DomainModel\AbstractConfiguration;

/**
 * Class MultipleChoiceEditorConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class MatchingEditorConfiguration extends AbstractConfiguration {

    const SHUFFLE_NONE = 0;
    const SHUFFLE_DEFINITIONS = 1;
    const SHUFFLE_TERMS = 2;
    const SHUFFLE_BOTH = 3;
  
    /**
     * @var int
     */
    protected $shuffle;

    /**
     * @var int
     */
    protected $thumbnail_size;
    
    const MATCHING_ONE_TO_ONE = 0;
    const MATCHING_MANY_TO_ONE = 1;
    const MATCHING_MANY_TO_MANY = 2;
    
    /**
     * @var int
     */
    protected $matching_mode;
    
    /**
     * @var array
     */
    protected $definitions;
    
    /**
     * @var array
     */
    protected $terms;
    
    /**
     * @var array
     */
    protected $matches;
    
    public static function create(
            int $shuffle = self::SHUFFLE_NONE,
            int $thumbnail_size = 100,
            int $matching_mode = self::MATCHING_ONE_TO_ONE,
            array $definitions = [],
            array $terms = [],
            array $matches = []
        ) : MatchingEditorConfiguration
    {
        $object = new MatchingEditorConfiguration();
        $object->shuffle = $shuffle;
        $object->thumbnail_size = $thumbnail_size;
        $object->matching_mode = $matching_mode;
        $object->definitions = $definitions;
        $object->terms = $terms;
        $object->matches = $matches;
        return $object;
    }
    
    /**
     * @return int
     */
    public function getShuffle()
    {
        return $this->shuffle;
    }

    /**
     * @return int
     */
    public function getThumbnailSize()
    {
        return $this->thumbnail_size;
    }

    /**
     * @return int
     */
    public function getMatchingMode()
    {
        return $this->matching_mode;
    }

    /**
     * @return array
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * @return array
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * @return array
     */
    public function getMatches()
    {
        return $this->matches;
    }

    public function equals(AbstractValueObject $other): bool
    {
        /** @var MatchingEditorConfiguration $other */
        return get_class($this) === get_class($other) &&
               $this->getDefinitions() == $other->getDefinitions() &&
               $this->getMatches() == $other->getMatches() &&
               $this->getMatchingMode() === $other->getMatchingMode() &&
               $this->getShuffle() == $other->getShuffle() &&
               $this->getTerms() == $other->getTerms() &&
               $this->getThumbnailSize() === $other->getThumbnailSize();
    }
}