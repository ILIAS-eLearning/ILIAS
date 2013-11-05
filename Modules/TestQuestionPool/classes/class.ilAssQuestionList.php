<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Taxonomy/interfaces/interface.ilTaxAssignedItemInfo.php';

/**
 * Handles a list of questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/TestQuestionPool
 * 
 */
class ilAssQuestionList implements ilTaxAssignedItemInfo
{
	/**
	 * global ilDB object instance
	 *
	 * @var ilDB
	 */
	private $db = null;
	
	/**
	 * global ilLanguage object instance
	 *
	 * @var ilLanguage
	 */
	private $lng = null;
	
	/**
	 * global ilPluginAdmin object instance
	 *
	 * @var ilPluginAdmin
	 */
	private $pluginAdmin = null;
	
	/**
	 * object id of parent question container
	 *
	 * @var integer
	 */
	private $parentObjId = null;
	
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
	 * the questions loaded by set criteria
	 *
	 * @var array
	 */
	private $questions = array();
	
	/**
	 * Constructor
	 * 
	 * @param ilDB $db
	 * @param integer $parentObjId
	 */
	public function __construct(ilDB $db, ilLanguage $lng, ilPluginAdmin $pluginAdmin, $parentObjId)
	{
		$this->db = $db;
		$this->lng = $lng;
		$this->pluginAdmin = $pluginAdmin;
		$this->parentObjId = $parentObjId;
	}
	
	public function addFieldFilter($fieldName, $fieldValue)
	{
		$this->fieldFilters[$fieldName] = $fieldValue;
	}
	
	public function addTaxonomyFilter($taxId, $taxNodes)
	{
		$this->taxFilters[$taxId] = $taxNodes;
	}
	
	public function setAvailableTaxonomyIds($availableTaxonomyIds)
	{
		$this->availableTaxonomyIds = $availableTaxonomyIds;
	}
	
	public function getAvailableTaxonomyIds()
	{
		return $this->availableTaxonomyIds;
	}
	
	private function getFieldFilterExpressions()
	{
		$expressions = array();
		
		foreach($this->fieldFilters as $fieldName => $fieldValue)
		{
			switch($fieldName)
			{
				case 'title':
				case 'description':
				case 'author':
					
					$expressions[] = $this->db->like('qpl_questions.' . $fieldName, 'text', "%%$fieldValue%%");
					break;
					
				case 'type':
					
					$expressions[] = "qpl_qst_type.type_tag = {$this->db->quote($fieldValue, 'text')}";
					break;
			}
		}
		
		return $expressions;
	}
	
	private function getTaxonomyFilterExpressions()
	{
		$expressions = array();

		require_once 'Services/Taxonomy/classes/class.ilTaxonomyTree.php';
		require_once 'Services/Taxonomy/classes/class.ilTaxNodeAssignment.php';

		foreach($this->taxFilters as $taxId => $taxNodes)
		{
			$questionIds = array();

			$forceBypass = true;

			foreach($taxNodes as $taxNode)
			{
				$forceBypass = false;
				
				$taxTree = new ilTaxonomyTree($taxId);
				
				// alex: $this->parentObjId contains object id of pool?
				$taxNodeAssignment = new ilTaxNodeAssignment('qpl', $this->parentObjId, 'quest', $taxId);

				$subNodes = $taxTree->getSubTreeIds($taxNode);
				$subNodes[] = $taxNode;
				
				$taxItems = $taxNodeAssignment->getAssignmentsOfNode($subNodes);
				
				foreach($taxItems as $taxItem)
				{
					$questionIds[$taxItem['item_id']] = $taxItem['item_id'];
				}
			}

			if( !$forceBypass )
			{
				$expressions[] = $this->db->in('question_id', $questionIds, false, 'integer');
			}
		}

		return $expressions;
	}
	
	private function getConditionalExpression()
	{
		$CONDITIONS = array_merge(
				$this->getFieldFilterExpressions(),
				$this->getTaxonomyFilterExpressions()
		);
		
		$CONDITIONS = implode(' AND ', $CONDITIONS);
		
		return strlen($CONDITIONS) ? 'AND '.$CONDITIONS : '';
	}
	
	public function load()
	{		
		$query = "
			SELECT		qpl_questions.*,
						qpl_qst_type.type_tag,
						qpl_qst_type.plugin
			FROM		qpl_questions,
						qpl_qst_type
			WHERE		qpl_questions.original_id IS NULL
			AND			qpl_questions.tstamp > 0
			AND			qpl_questions.question_type_fi = qpl_qst_type.question_type_id
			AND			qpl_questions.obj_fi = %s
			
			{$this->getConditionalExpression()}
		";
		
		$res = $this->db->queryF($query, array('integer'), array($this->parentObjId));
		
		while( $row = $this->db->fetchAssoc($res) )
		{
			if( !$this->isActiveQuestionType($row) )
			{
				continue;
			}
				
			$row['taxonomies'] = $this->loadTaxonomyAssignmentData($row['question_id']);

			$row['ttype'] = $this->lng->txt($row['type_tag']);
			
			$this->questions[ $row['question_id'] ] = $row;
		}
	}
	
	private function loadTaxonomyAssignmentData($questionId)
	{
		$taxAssignmentData = array();

		foreach($this->getAvailableTaxonomyIds() as $taxId)
		{
			require_once 'Services/Taxonomy/classes/class.ilTaxonomyTree.php';
			require_once 'Services/Taxonomy/classes/class.ilTaxNodeAssignment.php';
		
			$taxTree = new ilTaxonomyTree($taxId);
			
			// alex: $this->parentObjId contains object id of pool?
			$taxAssignment = new ilTaxNodeAssignment('qpl', $this->parentObjId, 'quest', $taxId);
			
			$assignments = $taxAssignment->getAssignmentsOfItem($questionId);
			
			foreach($assignments as $assData)
			{
				if( !isset($taxAssignmentData[ $assData['tax_id'] ]) )
				{
					$taxAssignmentData[ $assData['tax_id'] ] = array();
				}
				
				$nodeData = $taxTree->getNodeData($assData['node_id']);
				
				$assData['node_lft'] = $nodeData['lft'];
				
				$taxAssignmentData[ $assData['tax_id'] ][ $assData['node_id'] ] = $assData;
			}
		}
		
		return $taxAssignmentData;
	}
	
	private function isActiveQuestionType($questionData)
	{
		if( !isset($questionData['plugin']) )
		{
			return false;
		}
		
		if( !$questionData['plugin'] )
		{
			return true;
		}
		
		return $this->pluginAdmin->isActive(IL_COMP_MODULE, 'TestQuestionPool', 'qst', $questionData['type_tag']);
	}
	
	public function getQuestionDataArray()
	{
		return $this->questions;
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
	public function getTitle($a_comp_id, $a_item_type, $a_item_id)
	{
		if( $a_comp_id != 'qpl' || $a_item_type != 'quest' || !(int)$a_item_id )
		{
			return '';
		}
		
		if( !isset($this->questions[$a_item_id]) )
		{
			return '';
		}
		
		return $this->questions[$a_item_id]['title'];
	}
}
