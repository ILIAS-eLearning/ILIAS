<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionType.php';

/**
 * Handles a list of questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 *
 */
class ilTestRandomQuestionSetStagingPoolQuestionList implements Iterator
{
    /**
     * @var ilDBInterface
     */
    private $db = null;
    
    /**
     * @var ilPluginAdmin
     */
    private $pluginAdmin = null;

    /**
     * @var integer
     */
    private $testObjId = -1;

    /**
     * @var integer
     */
    private $testId = -1;

    /**
     * @var integer
     */
    private $poolId = -1;

    /**
     * @var array
     */
    private $taxFilters = array();
    
    // fau: taxFilter/typeFilter - private variable
    // TODO-RND2017: rename to typesFilter (multiple types allowed)
    /**
     * @var array
     */
    private $typeFilter = array();
    // fau.
    
    /**
     * @var array
     */
    private $questions = array();

    /**
     * @param ilDB $db
     * @param ilPluginAdmin $pluginAdmin
     */
    public function __construct(ilDBInterface $db, ilPluginAdmin $pluginAdmin)
    {
        $this->db = $db;
        $this->pluginAdmin = $pluginAdmin;
    }

    public function setTestObjId($testObjId)
    {
        $this->testObjId = $testObjId;
    }

    public function getTestObjId()
    {
        return $this->testObjId;
    }

    public function setTestId($testId)
    {
        $this->testId = $testId;
    }

    public function getTestId()
    {
        return $this->testId;
    }

    public function setPoolId($poolId)
    {
        $this->poolId = $poolId;
    }

    public function getPoolId()
    {
        return $this->poolId;
    }

    public function addTaxonomyFilter($taxId, $taxNodes)
    {
        $this->taxFilters[$taxId] = $taxNodes;
    }

    public function getTaxonomyFilters()
    {
        return $this->taxFilters;
    }
    
    // fau: taxFilter/typeFilter - getter/setter
    public function getTypeFilter()
    {
        return $this->typeFilter;
    }
    
    public function setTypeFilter($typeFilter)
    {
        $this->typeFilter = $typeFilter;
    }
    // fau.

    public function loadQuestions()
    {
        $query = "
			SELECT		qpl_questions.question_id,
						qpl_qst_type.type_tag,
						qpl_qst_type.plugin,
						qpl_qst_type.plugin_name

			FROM		tst_rnd_cpy

			INNER JOIN	qpl_questions
			ON			qpl_questions.question_id = tst_rnd_cpy.qst_fi

			INNER JOIN	qpl_qst_type
			ON			qpl_qst_type.question_type_id = qpl_questions.question_type_fi

			WHERE		tst_rnd_cpy.tst_fi = %s
			AND			tst_rnd_cpy.qpl_fi = %s

			{$this->getConditionalExpression()}
		";
        
        $res = $this->db->queryF(
            $query,
            array('integer', 'integer'),
            array($this->getTestId(), $this->getPoolId())
        );
        
        //echo sprintf($query, $this->getTestId(), $this->getPoolId());exit;
        
        while ($row = $this->db->fetchAssoc($res)) {
            $row = ilAssQuestionType::completeMissingPluginName($row);
            
            if (!$this->isActiveQuestionType($row)) {
                continue;
            }

            $this->questions[] = $row['question_id'];
        }
    }

    private function getConditionalExpression()
    {
        $CONDITIONS = $this->getTaxonomyFilterExpressions();
        
        // fau: taxFilter/typeFilter - add the type filter expression to conditions
        $CONDITIONS = array_merge($CONDITIONS, $this->getTypeFilterExpressions());
        // fau.

        $CONDITIONS = implode(' AND ', $CONDITIONS);

        return strlen($CONDITIONS) ? 'AND ' . $CONDITIONS : '';
    }

    private function getTaxonomyFilterExpressions()
    {
        $expressions = array();

        require_once 'Services/Taxonomy/classes/class.ilTaxonomyTree.php';
        require_once 'Services/Taxonomy/classes/class.ilTaxNodeAssignment.php';

        foreach ($this->getTaxonomyFilters() as $taxId => $taxNodes) {
            $questionIds = array();

            $forceBypass = true;

            foreach ($taxNodes as $taxNode) {
                $forceBypass = false;

                $taxTree = new ilTaxonomyTree($taxId);

                $taxNodeAssignment = new ilTaxNodeAssignment('tst', $this->getTestObjId(), 'quest', $taxId);

                $subNodes = $taxTree->getSubTreeIds($taxNode);
                $subNodes[] = $taxNode;

                $taxItems = $taxNodeAssignment->getAssignmentsOfNode($subNodes);

                foreach ($taxItems as $taxItem) {
                    $questionIds[$taxItem['item_id']] = $taxItem['item_id'];
                }
            }

            if (!$forceBypass) {
                $expressions[] = $this->db->in('question_id', $questionIds, false, 'integer');
            }
        }

        return $expressions;
    }
    
    // fau: taxFilter/typeFilter - get the expressions for a type filter
    private function getTypeFilterExpressions()
    {
        if (count($this->typeFilter)) {
            return array(
                $this->db->in('question_type_fi', $this->typeFilter, false, 'integer')
            );
        }
        
        return array();
    }
    // fau;

    private function isActiveQuestionType($questionData)
    {
        if (!isset($questionData['plugin'])) {
            return false;
        }
        
        if (!$questionData['plugin']) {
            return true;
        }
        
        return $this->pluginAdmin->isActive(IL_COMP_MODULE, 'TestQuestionPool', 'qst', $questionData['plugin_name']);
    }

    public function resetQuestionList()
    {
        $this->questions = array();
        $this->taxFilters = array();

        $this->testObjId = -1;
        $this->testId = -1;
        $this->poolId = -1;
    }
    
    public function getQuestions()
    {
        return array_values($this->questions);
    }

    // =================================================================================================================

    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinition
     */
    public function rewind()
    {
        return reset($this->questions);
    }

    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinition
     */
    public function current()
    {
        return current($this->questions);
    }

    /**
     * @return integer
     */
    public function key()
    {
        return key($this->questions);
    }

    /**
     * @return ilTestRandomQuestionSetSourcePoolDefinition
     */
    public function next()
    {
        return next($this->questions);
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return key($this->questions) !== null;
    }
    
    public static function updateSourceQuestionPoolId($testId, $oldPoolId, $newPoolId)
    {
        $db = $GLOBALS['DIC']['ilDB'];
        
        $query = "UPDATE tst_rnd_cpy SET qpl_fi = %s WHERE tst_fi = %s AND qpl_fi = %s";
        
        $db->manipulateF(
            $query,
            array('integer', 'integer', 'integer'),
            array($newPoolId, $testId, $oldPoolId)
        );
    }
}
