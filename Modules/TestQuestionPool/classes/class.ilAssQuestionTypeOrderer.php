<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Orderer for Question Type Lists
 *
 * @author		Björn Heyser <bheyser@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup ModulesTestQuestionPool
 * @package Modules/TestQuestionPool
 */
class ilAssQuestionTypeOrderer
{
    /**
     * order mode with fixed priority for ordering
     */
    const ORDER_MODE_FIX = 'fix';
    
    /**
     * order mode that orders by alphanumerical priority
     */
    const ORDER_MODE_ALPHA = 'alpha';
    
    /**
     * defines the fix order for question types
     *
     * @var array
     */
    public static $fixQuestionTypeOrder = array(
        'assSingleChoice',
        'assMultipleChoice',
        'assKprimChoice',
        
        'assErrorText',
        'assImagemapQuestion',

        'assClozeTest',
        'assNumeric',
        'assFormulaQuestion',
        'assTextSubset',

        'assOrderingQuestion',
        'assOrderingHorizontal',
        'assMatchingQuestion',

        'assTextQuestion',
        'assFileUpload',
        'assLongMenu',

        'assFlashQuestion',
        'assJavaApplet',
    );
    
    /**
     * @var array
     */
    protected $deprecatedTypes = array(
        'assFlashQuestion', 'assJavaApplet'
    );
    
    /**
     * flipped question type order (used for determining order priority)
     *
     * @var array
     */
    public static $flippedQuestionTypeOrder = null;
    
    /**
     * Constructor
     *
     * @param array $unOrderedTypes
     * @param string $orderMode
     * @throws ilTestQuestionPoolException
     */
    public function __construct($unOrderedTypes, $orderMode = self::ORDER_MODE_ALPHA)
    {
        self::$flippedQuestionTypeOrder = array_flip(self::$fixQuestionTypeOrder);
        #vd($unOrderedTypes);
        $this->types = $unOrderedTypes;
        
        switch ($orderMode) {
            case self::ORDER_MODE_FIX:
                
                uasort($this->types, array($this, 'fixQuestionTypeOrderSortCallback'));
                break;
            
            case self::ORDER_MODE_ALPHA:
                
                ksort($this->types);
                break;
            
            default:
                
                throw new ilTestQuestionPoolException('invalid order mode given: ' . $orderMode);
        }

        #vd($this->types);
    }
    
    /**
     * getter for ordered question types
     *
     * @return array $orderedQuestionTypes
     */
    public function getOrderedTypes($withDeprecatedTypes = true)
    {
        if ($withDeprecatedTypes) {
            return $this->types;
        }
        
        $types = array();
        
        foreach ($this->types as $translation => $typeData) {
            if (in_array($typeData['type_tag'], $this->deprecatedTypes)) {
                continue;
            }
            
            $types[$translation] = $typeData;
        }
        
        return $types;
    }
    
    /**
     * custom sort callback for ordering the question types
     *
     * @access public
     * @param array $a
     * @param array $b
     * @return integer
     */
    public function fixQuestionTypeOrderSortCallback($a, $b)
    {
        if (self::$flippedQuestionTypeOrder[ $a['type_tag'] ] > self::$flippedQuestionTypeOrder[ $b['type_tag'] ]) {
            return 1;
        } elseif (!isset(self::$flippedQuestionTypeOrder[ $a['type_tag'] ])) {
            return 1;
        } elseif (self::$flippedQuestionTypeOrder[ $a['type_tag'] ] < self::$flippedQuestionTypeOrder[ $b['type_tag'] ]) {
            return -1;
        } elseif (!isset(self::$flippedQuestionTypeOrder[ $b['type_tag'] ])) {
            return -1;
        }

        return 0;
    }
}
