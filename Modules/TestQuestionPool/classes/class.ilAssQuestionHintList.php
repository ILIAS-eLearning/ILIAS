<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHint.php';

/**
 * Model class for managing lists of hints for a question
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionHintList implements Iterator
{
    /**
     * the hint items array
     *
     * @access	private
     * @var		array
     */
    private $questionHints = array();
    
    /**
     * iterator interface method
     *
     * @access	public
     * @return	mixed
     */
    public function current()
    {
        return current($this->questionHints);
    }

    /**
     * iterator interface method
     *
     * @access	public
     * @return	mixed
     */
    public function rewind()
    {
        return reset($this->questionHints);
    }
    
    /**
     * iterator interface method
     *
     * @access	public
     * @return	mixed
     */
    public function next()
    {
        return next($this->questionHints);
    }
    
    /**
     * iterator interface method
     *
     * @access	public
     * @return	mixed
     */
    public function key()
    {
        return key($this->questionHints);
    }
    
    /**
     * iterator interface method
     *
     * @access	public
     * @return	boolean
     */
    public function valid()
    {
        return key($this->questionHints) !== null;
    }
    
    /**
     * Constructor
     *
     * @access	public
     */
    public function __construct()
    {
    }
    
    /**
     * adds a question hint object to the current list instance
     *
     * @access	public
     * @param	ilAssQuestionHint	$questionHint
     */
    public function addHint(ilAssQuestionHint $questionHint)
    {
        $this->questionHints[] = $questionHint;
    }
    
    /**
     * returns the question hint object relating to the passed hint id
     *
     * @access	public
     * @param	integer				$hintId
     * @return	ilAssQuestionHint	$questionHint
     */
    public function getHint($hintId)
    {
        foreach ($this as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */

            if ($questionHint->getId() == $hintId) {
                return $questionHint;
            }
        }
        
        require_once 'Modules/TestQuestionPool/exceptions/class.ilTestQuestionPoolException.php';
        throw new ilTestQuestionPoolException("hint with id $hintId does not exist in this list");
    }
    
    /**
     * checks wether a question hint object
     * relating to the passed id exists or not
     *
     * @access	public
     * @param	integer		$hintId
     * @return	boolean		$hintExists
     */
    public function hintExists($hintId)
    {
        foreach ($this as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */

            if ($questionHint->getId() == $hintId) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * re-indexes the list's hints sequentially by current order (starting with index "1")
     *
     * ATTENTION: it also persists this index to db by performing an update of hint object via id.
     * do not re-index any hint list objects unless this lists contain ALL hint objects for a SINGLE question
     * and no more hints apart of that.
     *
     * @access	public
     */
    public function reIndex()
    {
        $counter = 0;
        
        foreach ($this as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */
            
            $questionHint->setIndex(++$counter);
            $questionHint->save();
        }
    }
    
    /**
     * duplicates a hint list from given original question id to
     * given duplicate question id and returns an array of duplicate hint ids
     * mapped to the corresponding original hint ids
     *
     * @param integer $originalQuestionId
     * @param integer $duplicateQuestionId
     * @return array $hintIds containing the map from original hint ids to duplicate hint ids
     */
    public static function duplicateListForQuestion($originalQuestionId, $duplicateQuestionId)
    {
        $hintIds = array();
        
        $questionHintList = self::getListByQuestionId($originalQuestionId);

        foreach ($questionHintList as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */
            
            $originalHintId = $questionHint->getId();
            
            $questionHint->setId(null);
            $questionHint->setQuestionId($duplicateQuestionId);
            
            $questionHint->save();
            
            $duplicateHintId = $questionHint->getId();
            
            $hintIds[$originalHintId] = $duplicateHintId;
        }
        
        return $hintIds;
    }
    
    /**
     * returns an array with data of the hints in this list
     * that is adopted to be used as table gui data
     *
     * @access	public
     * @return	array	$tableData
     */
    public function getTableData()
    {
        $tableData = array();
        
        foreach ($this as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */

            $tableData[] = array(
                'hint_id' => $questionHint->getId(),
                'hint_index' => $questionHint->getIndex(),
                'hint_points' => $questionHint->getPoints(),
                'hint_text' => $questionHint->getText()
            );
        }
        
        return $tableData;
    }
    
    /**
     * instantiates a question hint list for the passed question id
     *
     * @access	public
     * @static
     * @global	ilDBInterface	$ilDB
     * @param	integer	$questionId
     * @return	self	$questionHintList
     */
    public static function getListByQuestionId($questionId)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $query = "
			SELECT		qht_hint_id,
						qht_question_fi,
						qht_hint_index,
						qht_hint_points,
						qht_hint_text
					
			FROM		qpl_hints
			
			WHERE		qht_question_fi = %s
			
			ORDER BY	qht_hint_index ASC
		";
        
        $res = $ilDB->queryF(
            $query,
            array('integer'),
            array((int) $questionId)
        );
        
        $questionHintList = new self();
        
        while ($row = $ilDB->fetchAssoc($res)) {
            $questionHint = new ilAssQuestionHint();
            
            ilAssQuestionHint::assignDbRow($questionHint, $row);
            
            $questionHintList->addHint($questionHint);
        }
        
        return $questionHintList;
    }
    
    /**
     * instantiates a question hint list for the passed hint ids
     *
     * @access	public
     * @static
     * @global	ilDBInterface	$ilDB
     * @param	array	$hintIds
     * @return	self	$questionHintList
     */
    public static function getListByHintIds($hintIds)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $qht_hint_id__IN__hintIds = $ilDB->in('qht_hint_id', $hintIds, false, 'integer');
        
        $query = "
			SELECT		qht_hint_id,
						qht_question_fi,
						qht_hint_index,
						qht_hint_points,
						qht_hint_text
					
			FROM		qpl_hints
			
			WHERE		$qht_hint_id__IN__hintIds
			
			ORDER BY	qht_hint_index ASC
		";
        
        $res = $ilDB->query($query);
        
        $questionHintList = new self();
        
        while ($row = $ilDB->fetchAssoc($res)) {
            $questionHint = new ilAssQuestionHint();
            
            ilAssQuestionHint::assignDbRow($questionHint, $row);
            
            $questionHintList->addHint($questionHint);
        }
        
        return $questionHintList;
    }
    
    /**
     * determines the next index to be used for a new hint
     * that is to be added to the list of existing hints
     * regarding to the question with passed question id
     *
     * @access	public
     * @static
     * @global	ilDBInterface		$ilDB
     * @param	integer		$questionId
     * @return	integer		$nextIndex
     */
    public static function getNextIndexByQuestionId($questionId)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
                
        $query = "
			SELECT		1 + COALESCE( MAX(qht_hint_index), 0 ) next_index
					
			FROM		qpl_hints
			
			WHERE		qht_question_fi = %s
		";
        
        $res = $ilDB->queryF(
            $query,
            array('integer'),
            array((int) $questionId)
        );
        
        $row = $ilDB->fetchAssoc($res);
        
        return $row['next_index'];
    }
    
    /**
     * Deletes all question hints relating to questions included in given question ids
     *
     * @global ilDBInterface	$ilDB
     * @param array[integer] $questionIds
     */
    public static function deleteHintsByQuestionIds($questionIds)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $__qht_question_fi__IN__questionIds = $ilDB->in('qht_question_fi', $questionIds, false, 'integer');
        
        $query = "
			DELETE FROM		qpl_hints
			WHERE			$__qht_question_fi__IN__questionIds
		";
        
        return $ilDB->manipulate($query);
    }
}
