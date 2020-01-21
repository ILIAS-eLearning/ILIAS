<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/exceptions/class.ilTestQuestionPoolException.php';

/**
 * Model class for managing a question hint
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionHint
{
    const PAGE_OBJECT_TYPE = 'qht';
    
    /**
     * this is the primary key for a hint single hint
     *
     * @access	private
     * @var		integer
     */
    private $id = null;
    
    /**
     * the id of question this hint relates to
     *
     * @access	private
     * @var		integer
     */
    private $questionId = null;
    
    /**
     * a list of hints is offered step by step
     * regarding to the order based on this index
     *
     * @access	private
     * @var		integer
     */
    private $index = null;
    
    /**
     * the points the have to be ground-off
     * when a user resorts to this hint
     *
     * @access	private
     * @var		integer
     */
    private $points = null;
    
    /**
     * the hint text itself
     *
     * @access	private
     * @var		string
     */
    private $text = null;
    
    /**
     * Constructor
     *
     * @access	public
     */
    public function __construct()
    {
    }
    
    /**
     * returns the hint id
     *
     * @access	public
     * @return	integer	$id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * sets the passed hint id
     *
     * @access	public
     * @param	integer	$id
     */
    public function setId($id)
    {
        $this->id = (int) $id;
    }

    /**
     * returns the question id the hint currently relates to
     *
     * @access	public
     * @return	integer	$questionId
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }

    /**
     * sets the passed question id so hint relates to it
     *
     * @access	public
     * @param	integer	$questionId
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = (int) $questionId;
    }

    /**
     * returns the ordering index of hint
     *
     * @access	public
     * @return	integer	$index
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * sets the passed hint ordering index
     *
     * @access	public
     * @param	integer	$index
     */
    public function setIndex($index)
    {
        $this->index = (int) $index;
    }

    /**
     * returns the points to ground-off for this hint
     *
     * @access	public
     * @return	integer	$points
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * sets the passed points to ground-off for this hint
     *
     * @access	public
     * @param	integer	$points
     */
    public function setPoints($points)
    {
        $this->points = (float) $points;
    }

    /**
     * returns the hint text
     *
     * @access	public
     * @return	string	$text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * sets the passed hint text
     *
     * @access	public
     * @param	string	$text
     */
    public function setText($text)
    {
        $this->text = $text;
    }
    
    /**
     * loads the hint dataset with passed id from database
     * and assigns it the to this hint object instance
     *
     * @access	public
     * @global	ilDBInterface	$ilDB
     * @param	integer	$id
     * @return	boolean	$success
     */
    public function load($id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $query = "
			SELECT	qht_hint_id,
					qht_question_fi,
					qht_hint_index,
					qht_hint_points,
					qht_hint_text
					
			FROM	qpl_hints
			
			WHERE	qht_hint_id = %s
		";
        
        $res = $ilDB->queryF(
            $query,
            array('integer'),
            array((int) $id)
        );
        
        while ($row = $ilDB->fetchAssoc($res)) {
            self::assignDbRow($this, $row);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * saves the current hint object state to database. it performs an insert or update,
     * depending on the current initialisation of the hint id property
     *
     * a valid initialised id leads to an update, a non or invalid initialised id leads to an insert
     *
     * @access	public
     * @return	boolean	$success
     */
    public function save()
    {
        if ($this->getId()) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }
    
    /**
     * persists the current object state to database by updating
     * an existing dataset identified by hint id
     *
     * @access	private
     * @global	ilDBInterface	$ilDB
     * @return	boolean	$success
     */
    private function update()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        return $ilDB->update(
            'qpl_hints',
            array(
                    'qht_question_fi'	=> array('integer', $this->getQuestionId()),
                    'qht_hint_index'	=> array('integer', $this->getIndex()),
                    'qht_hint_points'	=> array('float', $this->getPoints()),
                    'qht_hint_text'		=> array('clob', $this->getText())
                ),
            array(
                    'qht_hint_id'		=> array('integer', $this->getId())
                )
        );
    }
    
    /**
     * persists the current object state to database by inserting
     * a new dataset with a new hint id fetched from primary key sequence
     *
     * @access	private
     * @global	ilDBInterface	$ilDB
     * @return	boolean	$success
     */
    private function insert()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $this->setId($ilDB->nextId('qpl_hints'));
        
        return $ilDB->insert('qpl_hints', array(
            'qht_hint_id'		=> array('integer', $this->getId()),
            'qht_question_fi'	=> array('integer', $this->getQuestionId()),
            'qht_hint_index'	=> array('integer', $this->getIndex()),
            'qht_hint_points'	=> array('float', $this->getPoints()),
            'qht_hint_text'		=> array('clob', $this->getText())
        ));
    }
    
    /**
     * deletes the persisted hint object in database by deleting
     * the hint dataset identified by hint id
     *
     * @return	integer	$affectedRows
     */
    public function delete()
    {
        return self::deleteById($this->getId());
    }
    
    /**
     * assigns the field elements of passed hint db row array to the
     * corresponding hint object properties of passed hint object instance
     *
     * @access	public
     * @static
     * @param	self	$questionHint
     * @param	array	$hintDbRow
     */
    public static function assignDbRow(self $questionHint, $hintDbRow)
    {
        foreach ($hintDbRow as $field => $value) {
            switch ($field) {
                case 'qht_hint_id':			$questionHint->setId($value); break;
                case 'qht_question_fi':		$questionHint->setQuestionId($value); break;
                case 'qht_hint_index':		$questionHint->setIndex($value); break;
                case 'qht_hint_points':		$questionHint->setPoints($value); break;
                case 'qht_hint_text':		$questionHint->setText($value); break;
                
                default:	throw new ilTestQuestionPoolException("invalid db field identifier ($field) given!");
            }
        }
    }
    
    /**
     * deletes the persisted hint object in database by deleting
     * the hint dataset identified by hint id
     *
     * @access	public
     * @static
     * @global	ilDBInterface	$ilDB
     * @param	integer	$hintId
     * @return	integer	$affectedRows
     */
    public static function deleteById($hintId)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $query = "
			DELETE FROM		qpl_hints
			WHERE			qht_hint_id = %s
		";
        
        return $ilDB->manipulateF(
            $query,
            array('integer'),
            array($hintId)
        );
    }
    
    /**
     * creates a hint object instance, loads the persisted hint dataset
     * identified by passed hint id from database and assigns it as object state
     *
     * @access	public
     * @static
     * @param	integer	$hintId
     * @return	self	$hintInstance
     */
    public static function getInstanceById($hintId)
    {
        $questionHint = new self();
        $questionHint->load($hintId);
        return $questionHint;
    }
    
    public function getPageObjectType()
    {
        return self::PAGE_OBJECT_TYPE;
    }
    
    public static function getHintIndexLabel(ilLanguage $lng, $hintIndex)
    {
        return sprintf($lng->txt('tst_question_hints_index_column_label'), $hintIndex);
    }
}
