<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionCollectionSubsetApplication.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test
 */
class ilTestRandomQuestionCollectionSubsetApplicationList implements Iterator
{
    /**
     * @var ilTestRandomQuestionCollectionSubsetApplication[]
     */
    protected $collectionSubsetApplications = array();
    
    /**
     * @var ilTestRandomQuestionSetQuestionCollection
     */
    protected $reservedQuestionCollection;
    
    /**
     * ilTestRandomQuestionCollectionSubsetApplicantList constructor.
     */
    public function __construct()
    {
        $this->setReservedQuestionCollection(new ilTestRandomQuestionSetQuestionCollection());
    }
    
    /**
     * @param integer $applicantId
     * @return ilTestRandomQuestionCollectionSubsetApplication
     */
    public function getCollectionSubsetApplication($applicantId)
    {
        if (!isset($this->collectionSubsetApplications[$applicantId])) {
            return null;
        }
        
        return $this->collectionSubsetApplications[$applicantId];
    }
    
    /**
     * @return ilTestRandomQuestionCollectionSubsetApplication[]
     */
    public function getCollectionSubsetApplications()
    {
        return $this->collectionSubsetApplications;
    }
    
    /**
     * @param ilTestRandomQuestionCollectionSubsetApplicant $collectionSubsetApplicant
     */
    public function addCollectionSubsetApplication(ilTestRandomQuestionCollectionSubsetApplication $collectionSubsetApplication)
    {
        $this->collectionSubsetApplications[$collectionSubsetApplication->getApplicantId()] = $collectionSubsetApplication;
    }
    
    /**
     * @param ilTestRandomQuestionCollectionSubsetApplication[] $collectionSubsetApplications
     */
    public function setCollectionSubsetApplications($collectionSubsetApplications)
    {
        $this->collectionSubsetApplications = $collectionSubsetApplications;
    }
    
    /**
     * resetter for collectionSubsetApplicants
     */
    public function resetCollectionSubsetApplicants()
    {
        $this->setCollectionSubsetApplications(array());
    }
    
    /**
     * @return ilTestRandomQuestionSetQuestionCollection
     */
    public function getReservedQuestionCollection()
    {
        return $this->reservedQuestionCollection;
    }
    
    /**
     * @param ilTestRandomQuestionSetQuestionCollection $reservedQuestionCollection
     */
    public function setReservedQuestionCollection($reservedQuestionCollection)
    {
        $this->reservedQuestionCollection = $reservedQuestionCollection;
    }
    
    /**
     * @param ilTestRandomQuestionSetQuestion $question
     */
    public function addReservedQuestion(ilTestRandomQuestionSetQuestion $reservedQuestion)
    {
        $this->getReservedQuestionCollection()->addQuestion($reservedQuestion);
    }
    
    /* @return ilTestRandomQuestionCollectionSubsetApplication */
    public function current()
    {
        return current($this->collectionSubsetApplications);
    }
    /* @return ilTestRandomQuestionCollectionSubsetApplication */
    public function next()
    {
        return next($this->collectionSubsetApplications);
    }
    /* @return string */
    public function key()
    {
        return key($this->collectionSubsetApplications);
    }
    /* @return bool */
    public function valid()
    {
        return key($this->collectionSubsetApplications) !== null;
    }
    /* @return ilTestRandomQuestionCollectionSubsetApplication */
    public function rewind()
    {
        return reset($this->collectionSubsetApplications);
    }
    
    /**
     * @param ilTestRandomQuestionSetQuestion $question
     */
    public function handleQuestionRequest(ilTestRandomQuestionSetQuestion $question)
    {
        $questionReservationRequired = false;
        
        foreach ($this as $collectionSubsetApplication) {
            if (!$collectionSubsetApplication->hasQuestion($question->getQuestionId())) {
                continue;
            }
            
            if ($collectionSubsetApplication->hasRequiredAmountLeft()) {
                $questionReservationRequired = true;
                $collectionSubsetApplication->decrementRequiredAmount();
            }
        }
        
        if ($questionReservationRequired) {
            $this->addReservedQuestion($question);
        }
    }
    
    /**
     * @return int
     */
    public function getNonReservedQuestionAmount()
    {
        $availableQuestionCollection = new ilTestRandomQuestionSetQuestionCollection();
        
        foreach ($this as $collectionSubsetApplication) {
            $applicationsNonReservedQstCollection = $collectionSubsetApplication->getRelativeComplementCollection(
                $this->getReservedQuestionCollection()
            );
            
            $availableQuestionCollection->mergeQuestionCollection($applicationsNonReservedQstCollection);
        }
        
        $nonReservedQuestionCollection = $availableQuestionCollection->getUniqueQuestionCollection();
        
        return $nonReservedQuestionCollection->getQuestionAmount();
    }
}
