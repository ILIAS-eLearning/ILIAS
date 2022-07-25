<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionPreviewSession
{
    const SESSION_BASEINDEX = 'ilAssQuestionPreviewSessions';
    
    const SESSION_SUBINDEX_INSTANT_RESPONSE_ACTIVE = 'instantResponseActive';
    const SESSION_SUBINDEX_PARTICIPANT_SOLUTION = 'participantSolution';
    const SESSION_SUBINDEX_REQUESTED_HINTS = 'requestedHints';
    const SESSION_SUBINDEX_RANDOMIZER_SEED = 'randomizerSeed';

    private $userId;
    private $questionId;
    
    public function __construct($userId, $questionId)
    {
        $this->userId = $userId;
        $this->questionId = $questionId;
    }
    
    public function init() : void
    {
        $this->ensureSessionStructureExists();
    }
    
    public function getUserId()
    {
        return $this->userId;
    }
    
    public function getQuestionId()
    {
        return $this->questionId;
    }
    
    private function getSessionContextIndex() : string
    {
        return "u{$this->userId}::q{$this->questionId}";
    }
    
    private function saveSessionValue($subIndex, $value) : void
    {
        $val = ilSession::get(self::SESSION_BASEINDEX);
        $val[$this->getSessionContextIndex()][$subIndex] = $value;
        ilSession::set(self::SESSION_BASEINDEX, $val);
        //$_SESSION[self::SESSION_BASEINDEX][$this->getSessionContextIndex()][$subIndex] = $value;
    }
    
    private function issetSessionValue($subIndex) : bool
    {
        $val = ilSession::get(self::SESSION_BASEINDEX);
        return isset($val[$this->getSessionContextIndex()][$subIndex]);
        //return isset($_SESSION[self::SESSION_BASEINDEX][$this->getSessionContextIndex()][$subIndex]);
    }
    
    private function readSessionValue($subIndex)
    {
        $val = ilSession::get(self::SESSION_BASEINDEX);
        return $val[$this->getSessionContextIndex()][$subIndex] ?? [];
        //return $_SESSION[self::SESSION_BASEINDEX][$this->getSessionContextIndex()][$subIndex];
    }

    public function setInstantResponseActive($instantResponseActive) : void
    {
        $this->saveSessionValue(self::SESSION_SUBINDEX_INSTANT_RESPONSE_ACTIVE, $instantResponseActive);
    }
    
    public function isInstantResponseActive()
    {
        return $this->readSessionValue(self::SESSION_SUBINDEX_INSTANT_RESPONSE_ACTIVE);
    }
    
    public function setParticipantsSolution($participantSolution) : void
    {
        $this->saveSessionValue(self::SESSION_SUBINDEX_PARTICIPANT_SOLUTION, $participantSolution);
    }

    public function getParticipantsSolution()
    {
        return $this->readSessionValue(self::SESSION_SUBINDEX_PARTICIPANT_SOLUTION);
    }
    
    public function hasParticipantSolution() : bool
    {
        return $this->issetSessionValue(self::SESSION_SUBINDEX_PARTICIPANT_SOLUTION);
    }
    
    public function getNumRequestedHints() : int
    {
        if (!$this->issetSessionValue(self::SESSION_SUBINDEX_REQUESTED_HINTS)) {
            return 0;
        }
        $hints = $this->readSessionValue(self::SESSION_SUBINDEX_REQUESTED_HINTS);

        if (!is_array($hints)) {
            return 0;
        }

        return count($hints);
    }
    
    public function isHintRequested($hintId) : bool
    {
        if ($this->issetSessionValue(self::SESSION_SUBINDEX_REQUESTED_HINTS)) {
            $requestedHints = $this->readSessionValue(self::SESSION_SUBINDEX_REQUESTED_HINTS);
            return isset($requestedHints[$hintId]);
        }

        return false;
    }
    
    public function addRequestedHint($hintId) : void
    {
        $requestedHints = $this->getRequestedHints();
        $requestedHints[$hintId] = $hintId;
        $this->saveSessionValue(self::SESSION_SUBINDEX_REQUESTED_HINTS, $requestedHints);
    }
    
    public function getRequestedHints()
    {
        if ($this->issetSessionValue(self::SESSION_SUBINDEX_REQUESTED_HINTS)) {
            return $this->readSessionValue(self::SESSION_SUBINDEX_REQUESTED_HINTS);
        }

        return [];
    }
    
    public function resetRequestedHints() : void
    {
        $this->saveSessionValue(self::SESSION_SUBINDEX_REQUESTED_HINTS, array());
    }
    
    public function setRandomizerSeed($seed) : void
    {
        $this->saveSessionValue(self::SESSION_SUBINDEX_RANDOMIZER_SEED, $seed);
    }
    
    public function getRandomizerSeed()
    {
        return $this->readSessionValue(self::SESSION_SUBINDEX_RANDOMIZER_SEED);
    }

    public function randomizerSeedExists() : bool
    {
        return ($this->getRandomizerSeed() !== null);
    }

    private function ensureSessionStructureExists() : void
    {
        if (!is_array(ilSession::get(self::SESSION_BASEINDEX))) {
            ilSession::set(self::SESSION_BASEINDEX, array());
        }

        $baseSession = ilSession::get(self::SESSION_BASEINDEX);

        if (!isset($baseSession[$this->getSessionContextIndex()])) {
            $baseSession[$this->getSessionContextIndex()] = array();
        }

        $contextSession = &$baseSession[$this->getSessionContextIndex()];

        if (!isset($contextSession[self::SESSION_SUBINDEX_INSTANT_RESPONSE_ACTIVE])) {
            $contextSession[self::SESSION_SUBINDEX_INSTANT_RESPONSE_ACTIVE] = 0;
        }

        if (!isset($contextSession[self::SESSION_SUBINDEX_PARTICIPANT_SOLUTION])) {
            $contextSession[self::SESSION_SUBINDEX_PARTICIPANT_SOLUTION] = null;
        }

        if (!isset($contextSession[self::SESSION_SUBINDEX_RANDOMIZER_SEED])) {
            $contextSession[self::SESSION_SUBINDEX_RANDOMIZER_SEED] = null;
        }

        ilSession::set(self::SESSION_BASEINDEX, $baseSession);
    }
}
