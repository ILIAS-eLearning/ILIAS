<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilAssQuestionLifecycle
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionLifecycle
{
    const DRAFT = 'draft';
    const REVIEW = 'review';
    const REJECTED = 'rejected';
    const FINAL = 'final';
    const SHARABLE = 'sharable';
    const OUTDATED = 'outdated';
    
    /**
     * @var string
     */
    protected $identifier;
    
    /**
     * ilAssQuestionLifecycle constructor.
     */
    protected function __construct()
    {
        $this->setIdentifier(self::DRAFT);
    }
    
    /**
     * @return string
     */
    public function getIdentifier() : string
    {
        return $this->identifier;
    }
    
    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier) : void
    {
        $this->identifier = $identifier;
    }
    
    /**
     * @return string[]
     */
    public function getValidIdentifiers() : array
    {
        return [self::DRAFT, self::REVIEW, self::REJECTED, self::FINAL, self::SHARABLE, self::OUTDATED];
    }
    
    /**
     * @return string
     */
    public function getMappedLomLifecycle() : string
    {
        switch ($this->getIdentifier()) {
            case self::OUTDATED:
                
                return ilAssQuestionLomLifecycle::UNAVAILABLE;
            
            case self::SHARABLE:
            case self::FINAL:
                
                return ilAssQuestionLomLifecycle::FINAL;
            
            case self::REJECTED:
            case self::REVIEW:
            case self::DRAFT:
            default:
                
                return ilAssQuestionLomLifecycle::DRAFT;
        }
    }
    
    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getTranslation(ilLanguage $lng) : string
    {
        return $this->getTranslationByIdentifier($lng, $this->getIdentifier());
    }
    
    /**
     * @param ilLanguage $lng
     * @return string
     */
    protected function getTranslationByIdentifier(ilLanguage $lng, $identifier) : string
    {
        switch ($identifier) {
            case self::DRAFT:
                
                return $lng->txt('qst_lifecycle_draft');
            
            case self::REVIEW:
                
                return $lng->txt('qst_lifecycle_review');
            
            case self::REJECTED:
                
                return $lng->txt('qst_lifecycle_rejected');
            
            case self::FINAL:
                
                return $lng->txt('qst_lifecycle_final');
            
            case self::SHARABLE:
                
                return $lng->txt('qst_lifecycle_sharable');
            
            case self::OUTDATED:
                
                return $lng->txt('qst_lifecycle_outdated');
            
            default: return '';
        }
    }
    
    /**
     * @param ilLanguage $lng
     * @return array
     */
    public function getSelectOptions(ilLanguage $lng) : array
    {
        $selectOptions = [];
        
        foreach ($this->getValidIdentifiers() as $identifier) {
            $selectOptions[$identifier] = $this->getTranslationByIdentifier($lng, $identifier);
        }
        
        return $selectOptions;
    }
    
    /**
     * @param string $identifier
     * @throws ilTestQuestionPoolInvalidArgumentException
     */
    public function validateIdentifier($identifier) : void
    {
        if (!in_array($identifier, $this->getValidIdentifiers())) {
            throw new ilTestQuestionPoolInvalidArgumentException(
                'invalid ilias lifecycle given: ' . $identifier
            );
        }
    }
    
    /**
     * @param $identifier
     * @return ilAssQuestionLifecycle
     * @throws ilTestQuestionPoolInvalidArgumentException
     */
    public static function getInstance($identifier) : ilAssQuestionLifecycle
    {
        $lifecycle = new self();
        $lifecycle->validateIdentifier($identifier);
        $lifecycle->setIdentifier($identifier);
        
        return $lifecycle;
    }
    
    /**
     * @return ilAssQuestionLifecycle
     */
    public static function getDraftInstance() : ilAssQuestionLifecycle
    {
        $lifecycle = new self();
        $lifecycle->setIdentifier(self::DRAFT);
        
        return $lifecycle;
    }
}
