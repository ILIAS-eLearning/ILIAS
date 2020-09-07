<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class for managing a clipboard via php session, that is used to re order a hint list
 * (user cuts a hint with a first request and is able to paste it at another position with a further request)
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionHintsOrderingClipboard
{
    /**
     * the id of question the stored hint relates to
     *
     * @access	private
     * @var		integer
     */
    private $questionId = null;
    
    /**
     * Constructor
     *
     * @access	public
     * @param	assQuestion	$questionOBJ
     */
    public function __construct(assQuestion $questionOBJ)
    {
        $this->questionId = $questionOBJ->getId();
        
        if (!isset($_SESSION[__CLASS__])) {
            $_SESSION[__CLASS__] = array();
        }
        
        if (!isset($_SESSION[__CLASS__][$this->questionId])) {
            $_SESSION[__CLASS__][$this->questionId] = null;
        }
    }
    
    /**
     * resets the clipboard by ensuring no hint is stored
     *
     * @access	public
     */
    public function resetStored()
    {
        $_SESSION[__CLASS__][$this->questionId] = null;
    }
    
    /**
     * sets the passed hint id, so relating hint
     * is deemed to be cut to clipboard
     *
     * @access	public
     * @param	integer	$hintId
     */
    public function setStored($hintId)
    {
        $_SESSION[__CLASS__][$this->questionId] = $hintId;
    }
    
    /**
     * returns the hint id currently stored in clipboard
     *
     * @access	public
     * @return	integer $hintId
     */
    public function getStored()
    {
        return $_SESSION[__CLASS__][$this->questionId];
    }
    
    /**
     * returns the fact wether the hint relating to the passed hint id
     * is stored in clipboard or not
     *
     * @access	public
     * @param	integer	$hintId
     * @return	boolean	$isStored
     */
    public function isStored($hintId)
    {
        if ($_SESSION[__CLASS__][$this->questionId] === $hintId) {
            return true;
        }
        
        return false;
    }
    
    /**
     * returns the fact wether any hint is stored in clipboard currently or not
     *
     * @access	public
     * @return	boolean $hasStored
     */
    public function hasStored()
    {
        if ($_SESSION[__CLASS__][$this->questionId] !== null) {
            return true;
        }
        
        return false;
    }
}
