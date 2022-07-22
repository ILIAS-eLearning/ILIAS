<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Handles a list of questions
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/TestQuestionPool
 *
 */
class ilAssQuestionList implements ilTaxAssignedItemInfo
{
    private ilDBInterface $db;
    private ilLanguage $lng;
    private ilComponentRepository $component_repository;

    /**
     * object ids of parent question containers
     *
     * @var array
     */
    private $parentObjIdsFilter = array();

    /**
     * object id of parent question container
     *
     * @var integer
     */
    private $parentObjId = null;

    /**
     * object type of parent question container(s)
     *
     * @var string
     */
    private $parentObjType = 'qpl';
    
    /**
     * available taxonomy ids for current parent question container
     *
     * @var array
     */
    private $availableTaxonomyIds = array();
    
    /**
     * question field filters
     *
     * @var array
     */
    private $fieldFilters = array();

    /**
     * taxonomy filters
     *
     * @var array
     */
    private $taxFilters = array();

    /**
     * taxonomy parent ids
     *
     * @var array
     */
    private $taxParentIds = array();
    
    /**
     * taxonomy parent types
     *
     * @var array
     */
    private $taxParentTypes = array();
    
    /**
     * active id for determining answer status
     *
     * @var integer
     */
    private $answerStatusActiveId = null;

    /**
     * @var array
     */
    private $forcedQuestionIds = array();

    /**
     * should object_data table be joined?
     * @var bool
     */
    protected $join_obj_data = true;


    /**
     * answer status domain for single questions
     */
    const QUESTION_ANSWER_STATUS_NON_ANSWERED = 'nonAnswered';
    const QUESTION_ANSWER_STATUS_WRONG_ANSWERED = 'wrongAnswered';
    const QUESTION_ANSWER_STATUS_CORRECT_ANSWERED = 'correctAnswered';

    /**
     * answer status filter value domain
     */
    const ANSWER_STATUS_FILTER_ALL_NON_CORRECT = 'allNonCorrect';
    const ANSWER_STATUS_FILTER_NON_ANSWERED_ONLY = 'nonAnswered';
    const ANSWER_STATUS_FILTER_WRONG_ANSWERED_ONLY = 'wrongAnswered';
    
    /**
     * answer status filter
     *
     * @var string
     */
    private $answerStatusFilter = null;
    
    const QUESTION_INSTANCE_TYPE_ORIGINALS = 'QST_INSTANCE_TYPE_ORIGINALS';
    const QUESTION_INSTANCE_TYPE_DUPLICATES = 'QST_INSTANCE_TYPE_DUPLICATES';
    private $questionInstanceTypeFilter = self::QUESTION_INSTANCE_TYPE_ORIGINALS;
    
    private $includeQuestionIdsFilter = null;
    private $excludeQuestionIdsFilter = null;
    
    const QUESTION_COMPLETION_STATUS_COMPLETE = 'complete';
    const QUESTION_COMPLETION_STATUS_INCOMPLETE = 'incomplete';
    const QUESTION_COMPLETION_STATUS_BOTH = 'complete/incomplete';
    private $questionCompletionStatusFilter = self::QUESTION_COMPLETION_STATUS_BOTH;
    
    /**
     * the questions loaded by set criteria
     *
     * @var array
     */
    protected $questions = array();
    
    public function __construct(ilDBInterface $db, ilLanguage $lng, ilComponentRepository $component_repository)
    {
        $this->db = $db;
        $this->lng = $lng;
        $this->component_repository = $component_repository;
    }

    public function getParentObjId() : ?int
    {
        return $this->parentObjId;
    }

    public function setParentObjId($parentObjId) : void
    {
        $this->parentObjId = $parentObjId;
    }

    public function getParentObjectType() : string
    {
        return $this->parentObjType;
    }

    public function setParentObjectType($parentObjType) : void
    {
        $this->parentObjType = $parentObjType;
    }

    /**
     * @return array
     */
    public function getParentObjIdsFilter() : array
    {
        return $this->parentObjIdsFilter;
    }

    /**
     * @param array $parentObjIdsFilter
     */
    public function setParentObjIdsFilter($parentObjIdsFilter) : void
    {
        $this->parentObjIdsFilter = $parentObjIdsFilter;
    }

    public function setQuestionInstanceTypeFilter($questionInstanceTypeFilter) : void
    {
        $this->questionInstanceTypeFilter = $questionInstanceTypeFilter;
    }

    public function getQuestionInstanceTypeFilter()
    {
        return $this->questionInstanceTypeFilter;
    }

    public function setIncludeQuestionIdsFilter($questionIdsFilter) : void
    {
        $this->includeQuestionIdsFilter = $questionIdsFilter;
    }

    public function getIncludeQuestionIdsFilter()
    {
        return $this->includeQuestionIdsFilter;
    }

    public function getExcludeQuestionIdsFilter()
    {
        return $this->excludeQuestionIdsFilter;
    }
    
    public function setExcludeQuestionIdsFilter($excludeQuestionIdsFilter) : void
    {
        $this->excludeQuestionIdsFilter = $excludeQuestionIdsFilter;
    }

    public function getQuestionCompletionStatusFilter() : string
    {
        return $this->questionCompletionStatusFilter;
    }
    
    public function setQuestionCompletionStatusFilter($questionCompletionStatusFilter) : void
    {
        $this->questionCompletionStatusFilter = $questionCompletionStatusFilter;
    }

    public function addFieldFilter($fieldName, $fieldValue) : void
    {
        $this->fieldFilters[$fieldName] = $fieldValue;
    }
    
    public function addTaxonomyFilter($taxId, $taxNodes, $parentObjId, $parentObjType) : void
    {
        $this->taxFilters[$taxId] = $taxNodes;
        $this->taxParentIds[$taxId] = $parentObjId;
        $this->taxParentTypes[$taxId] = $parentObjType;
    }
    
    public function setAvailableTaxonomyIds($availableTaxonomyIds) : void
    {
        $this->availableTaxonomyIds = $availableTaxonomyIds;
    }
    
    public function getAvailableTaxonomyIds() : array
    {
        return $this->availableTaxonomyIds;
    }

    public function setAnswerStatusActiveId($answerStatusActiveId) : void
    {
        $this->answerStatusActiveId = $answerStatusActiveId;
    }

    public function getAnswerStatusActiveId() : ?int
    {
        return $this->answerStatusActiveId;
    }

    public function setAnswerStatusFilter($answerStatusFilter) : void
    {
        $this->answerStatusFilter = $answerStatusFilter;
    }

    public function getAnswerStatusFilter() : ?string
    {
        return $this->answerStatusFilter;
    }

    /**
     * Set if object data table should be joined
     *
     * @param bool $a_val join object_data
     */
    public function setJoinObjectData($a_val) : void
    {
        $this->join_obj_data = $a_val;
    }
    
    /**
     * Get if object data table should be joined
     *
     * @return bool join object_data
     */
    public function getJoinObjectData() : bool
    {
        return $this->join_obj_data;
    }
    
    /**
     * @param array $forcedQuestionIds
     */
    public function setForcedQuestionIds($forcedQuestionIds) : void
    {
        $this->forcedQuestionIds = $forcedQuestionIds;
    }

    /**
     * @return array
     */
    public function getForcedQuestionIds() : array
    {
        return $this->forcedQuestionIds;
    }
    
    private function getParentObjFilterExpression() : ?string
    {
        if ($this->getParentObjId()) {
            return 'qpl_questions.obj_fi = ' . $this->db->quote($this->getParentObjId(), 'integer');
        }
        
        if (count($this->getParentObjIdsFilter())) {
            return $this->db->in('qpl_questions.obj_fi', $this->getParentObjIdsFilter(), false, 'integer');
        }
        
        return null;
    }
    
    private function getFieldFilterExpressions() : array
    {
        $expressions = array();
        
        foreach ($this->fieldFilters as $fieldName => $fieldValue) {
            switch ($fieldName) {
                case 'title':
                case 'description':
                case 'author':
                case 'lifecycle':
                    
                    $expressions[] = $this->db->like('qpl_questions.' . $fieldName, 'text', "%%$fieldValue%%");
                    break;
                    
                case 'type':
                    
                    $expressions[] = "qpl_qst_type.type_tag = {$this->db->quote($fieldValue, 'text')}";
                    break;

                case 'question_id':
                    if ($fieldValue != "" && !is_array($fieldValue)) {
                        $fieldValue = array($fieldValue);
                    }
                    $expressions[] = $this->db->in("qpl_questions.question_id", $fieldValue, false, "integer");
                    break;
                
                case 'parent_title':
                    if ($this->join_obj_data) {
                        $expressions[] = $this->db->like('object_data.title', 'text', "%%$fieldValue%%");
                    }
                    break;
            }
        }
        
        return $expressions;
    }
    
    private function getTaxonomyFilterExpressions() : array
    {
        $expressions = array();

        require_once 'Services/Taxonomy/classes/class.ilTaxonomyTree.php';
        require_once 'Services/Taxonomy/classes/class.ilTaxNodeAssignment.php';

        foreach ($this->taxFilters as $taxId => $taxNodes) {
            $questionIds = array();

            $forceBypass = true;

            foreach ($taxNodes as $taxNode) {
                $forceBypass = false;

                $taxItemsByTaxParent = $this->getTaxItems(
                    $this->taxParentTypes[$taxId],
                    $this->taxParentIds[$taxId],
                    $taxId,
                    $taxNode
                );

                $taxItemsByParent = $this->getTaxItems(
                    $this->parentObjType,
                    $this->parentObjId,
                    $taxId,
                    $taxNode
                );

                $taxItems = array_merge($taxItemsByTaxParent, $taxItemsByParent);
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

    /**
     * @param string $parentType
     * @param int $parentObjId
     * @param int $taxId
     * @param int $taxNode
     * @return array
     */
    protected function getTaxItems($parentType, $parentObjId, $taxId, $taxNode) : array
    {
        $taxTree = new ilTaxonomyTree($taxId);

        $taxNodeAssignment = new ilTaxNodeAssignment(
            $parentType,
            $parentObjId,
            'quest',
            $taxId
        );

        $subNodes = $taxTree->getSubTreeIds($taxNode);
        $subNodes[] = $taxNode;

        return $taxNodeAssignment->getAssignmentsOfNode($subNodes);
    }

    private function getQuestionInstanceTypeFilterExpression() : ?string
    {
        switch ($this->getQuestionInstanceTypeFilter()) {
            case self::QUESTION_INSTANCE_TYPE_ORIGINALS:

                return 'qpl_questions.original_id IS NULL';

            case self::QUESTION_INSTANCE_TYPE_DUPLICATES:

                return 'qpl_questions.original_id IS NOT NULL';
        }

        return null;
    }

    private function getQuestionIdsFilterExpressions() : array
    {
        $expressions = array();
        
        if (is_array($this->getIncludeQuestionIdsFilter())) {
            $expressions[] = $this->db->in(
                'qpl_questions.question_id',
                $this->getIncludeQuestionIdsFilter(),
                false,
                'integer'
            );
        }

        if (is_array($this->getExcludeQuestionIdsFilter())) {
            $IN = $this->db->in(
                'qpl_questions.question_id',
                $this->getExcludeQuestionIdsFilter(),
                true,
                'integer'
            );
            
            if ($IN == ' 1=2 ') {
                $IN = ' 1=1 ';
            } // required for ILIAS < 5.0
            
            $expressions[] = $IN;
        }
        
        return $expressions;
    }
    
    private function getParentObjectIdFilterExpression() : ?string
    {
        if ($this->parentObjId) {
            return "qpl_questions.obj_fi = {$this->db->quote($this->parentObjId, 'integer')}";
        }
        
        return null;
    }
    
    private function getAnswerStatusFilterExpressions() : array
    {
        $expressions = array();
        
        switch ($this->getAnswerStatusFilter()) {
            case self::ANSWER_STATUS_FILTER_ALL_NON_CORRECT:
                
                $expressions[] = '
					(tst_test_result.question_fi IS NULL OR tst_test_result.points < qpl_questions.points)
				';
                break;
                
            case self::ANSWER_STATUS_FILTER_NON_ANSWERED_ONLY:

                $expressions[] = 'tst_test_result.question_fi IS NULL';
                break;

            case self::ANSWER_STATUS_FILTER_WRONG_ANSWERED_ONLY:
                
                $expressions[] = 'tst_test_result.question_fi IS NOT NULL';
                $expressions[] = 'tst_test_result.points < qpl_questions.points';
                break;
        }
        
        return $expressions;
    }
    
    private function getTableJoinExpression() : string
    {
        $tableJoin = "
			INNER JOIN	qpl_qst_type
			ON			qpl_qst_type.question_type_id = qpl_questions.question_type_fi
		";

        if ($this->join_obj_data) {
            $tableJoin .= "
				INNER JOIN	object_data
				ON			object_data.obj_id = qpl_questions.obj_fi
			";
        }
        
        if ($this->getAnswerStatusActiveId()) {
            $tableJoin .= "
				LEFT JOIN	tst_test_result
				ON			tst_test_result.question_fi = qpl_questions.question_id
				AND			tst_test_result.active_fi = {$this->db->quote($this->getAnswerStatusActiveId(), 'integer')}
			";
        }
        
        return $tableJoin;
    }
    
    private function getConditionalFilterExpression() : string
    {
        $CONDITIONS = array();

        if ($this->getQuestionInstanceTypeFilterExpression() !== null) {
            $CONDITIONS[] = $this->getQuestionInstanceTypeFilterExpression();
        }
        
        if ($this->getParentObjFilterExpression() !== null) {
            $CONDITIONS[] = $this->getParentObjFilterExpression();
        }

        if ($this->getParentObjectIdFilterExpression() !== null) {
            $CONDITIONS[] = $this->getParentObjectIdFilterExpression();
        }
        
        $CONDITIONS = array_merge(
            $CONDITIONS,
            $this->getQuestionIdsFilterExpressions(),
            $this->getFieldFilterExpressions(),
            $this->getTaxonomyFilterExpressions(),
            $this->getAnswerStatusFilterExpressions()
        );
        
        $CONDITIONS = implode(' AND ', $CONDITIONS);
        
        return strlen($CONDITIONS) ? 'AND ' . $CONDITIONS : '';
    }
    
    private function getSelectFieldsExpression() : string
    {
        $selectFields = array(
            'qpl_questions.*',
            'qpl_qst_type.type_tag',
            'qpl_qst_type.plugin',
            'qpl_qst_type.plugin_name',
            'qpl_questions.points max_points'
        );

        if ($this->join_obj_data) {
            $selectFields[] = 'object_data.title parent_title';
        }

        if ($this->getAnswerStatusActiveId()) {
            $selectFields[] = 'tst_test_result.points reached_points';
            $selectFields[] = "CASE
					WHEN tst_test_result.points IS NULL THEN '" . self::QUESTION_ANSWER_STATUS_NON_ANSWERED . "'
					WHEN tst_test_result.points < qpl_questions.points THEN '" . self::QUESTION_ANSWER_STATUS_WRONG_ANSWERED . "'
					ELSE '" . self::QUESTION_ANSWER_STATUS_CORRECT_ANSWERED . "'
				END question_answer_status
			";
        }

        $selectFields = implode(",\n\t\t\t\t", $selectFields);
        
        return "
			SELECT		{$selectFields}
		";
    }
    
    private function buildBasicQuery() : string
    {
        return "
			{$this->getSelectFieldsExpression()}
			
			FROM		qpl_questions
			
			{$this->getTableJoinExpression()}
			
			WHERE		qpl_questions.tstamp > 0
		";
    }
    
    private function buildQuery() : string
    {
        $query = $this->buildBasicQuery() . "
			{$this->getConditionalFilterExpression()}
		";
        
        if (count($this->getForcedQuestionIds())) {
            $query .= "
				UNION {$this->buildBasicQuery()}
				AND	{$this->db->in('qpl_questions.question_id', $this->getForcedQuestionIds(), false, 'integer')}
			";
        }

        return $query;
    }
    
    public function load() : void
    {
        $this->checkFilters();
        
        $query = $this->buildQuery();

        #vd($query);

        $res = $this->db->query($query);

        //echo $this->db->db->last_query;

        #vd($this->db->db->last_query);
        
        while ($row = $this->db->fetchAssoc($res)) {
            $row = ilAssQuestionType::completeMissingPluginName($row);
            
            if (!$this->isActiveQuestionType($row)) {
                continue;
            }

            $row['taxonomies'] = $this->loadTaxonomyAssignmentData($row['obj_fi'], $row['question_id']);
            
            $row['ttype'] = $this->lng->txt($row['type_tag']);
            
            $this->questions[ $row['question_id'] ] = $row;
        }
    }
    
    private function loadTaxonomyAssignmentData($parentObjId, $questionId) : array
    {
        $taxAssignmentData = array();

        foreach ($this->getAvailableTaxonomyIds() as $taxId) {
            require_once 'Services/Taxonomy/classes/class.ilTaxonomyTree.php';
            require_once 'Services/Taxonomy/classes/class.ilTaxNodeAssignment.php';
        
            $taxTree = new ilTaxonomyTree($taxId);
            
            $taxAssignment = new ilTaxNodeAssignment('qpl', $parentObjId, 'quest', $taxId);
            
            $assignments = $taxAssignment->getAssignmentsOfItem($questionId);
            
            foreach ($assignments as $assData) {
                if (!isset($taxAssignmentData[ $assData['tax_id'] ])) {
                    $taxAssignmentData[ $assData['tax_id'] ] = array();
                }
                
                $nodeData = $taxTree->getNodeData($assData['node_id']);
                
                $assData['node_lft'] = $nodeData['lft'];
                
                $taxAssignmentData[ $assData['tax_id'] ][ $assData['node_id'] ] = $assData;
            }
        }
        
        return $taxAssignmentData;
    }

    private function isActiveQuestionType(array $questionData) : bool
    {
        if (!isset($questionData['plugin'])) {
            return false;
        }
        
        if (!$questionData['plugin']) {
            return true;
        }

        if (!$this->component_repository->getComponentByTypeAndName(
            ilComponentInfo::TYPE_MODULES,
            'TestQuestionPool'
        )->getPluginSlotById('qst')->hasPluginName($questionData['plugin_name'])) {
            return false;
        }

        return $this->component_repository
            ->getComponentByTypeAndName(
                ilComponentInfo::TYPE_MODULES,
                'TestQuestionPool'
            )
            ->getPluginSlotById(
                'qst'
            )
            ->getPluginByName(
                $questionData['plugin_name']
            )->isActive();
    }

    public function getDataArrayForQuestionId($questionId)
    {
        return $this->questions[$questionId];
    }

    public function getQuestionDataArray() : array
    {
        return $this->questions;
    }

    public function isInList($questionId) : bool
    {
        return isset($this->questions[$questionId]);
    }

    /**
     * Get title of an assigned item
     *
     * (is used from ilObjTaxonomyGUI when item sorting is activated)
     *
     * @param string $a_comp_id ('qpl' in our context)
     * @param string $a_item_type ('quest' in our context)
     * @param integer $a_item_id (questionId in our context)
     */
    public function getTitle(string $a_comp_id, string $a_item_type, int $a_item_id) : string
    {
        if ($a_comp_id != 'qpl' || $a_item_type != 'quest' || !$a_item_id) {
            return '';
        }
        
        if (!isset($this->questions[$a_item_id])) {
            return '';
        }
        
        return $this->questions[$a_item_id]['title'];
    }
    
    private function checkFilters() : void
    {
        if (strlen($this->getAnswerStatusFilter()) && !$this->getAnswerStatusActiveId()) {
            require_once 'Modules/TestQuestionPool/exceptions/class.ilTestQuestionPoolException.php';
            
            throw new ilTestQuestionPoolException(
                'No active id given! You cannot use the answer status filter without giving an active id.'
            );
        }
    }
}
