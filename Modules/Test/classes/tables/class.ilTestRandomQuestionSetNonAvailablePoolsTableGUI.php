<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilTestRandomQuestionSetNonAvailablePoolsTableGUI extends ilTable2GUI
{
	const IDENTIFIER = 'NonAvailPoolsTbl';
	
	/**
	 * @var ilCtrl
	 */
	protected $ctrl = null;
	
	/**
	 * @var ilLanguage
	 */
	protected $lng;
	
	public function __construct(ilCtrl $ctrl, ilLanguage $lng, $parentGUI, $parentCMD)
	{
		parent::__construct($parentGUI, $parentCMD);
		
		$this->ctrl = $ctrl;
		$this->lng = $lng;
	}
	
	private function setTableIdentifiers()
	{
		$this->setId(self::IDENTIFIER);
		$this->setPrefix(self::IDENTIFIER);
		$this->setFormName(self::IDENTIFIER);
	}
	
	public function build()
	{
		$this->setTableIdentifiers();
		
		$this->setTitle($this->lng->txt('tst_lost_src_quest_pools_table'));
		
		$this->setRowTemplate('tpl.il_tst_lost_src_quest_pools_row.html', 'Modules/Test');
		
		$this->enable('header');
		$this->disable('sort');
		
		$this->enable('select_all');
		$this->setSelectAllCheckbox('lost_pool_ids[]');
		
		$this->setExternalSegmentation(true);
		$this->setLimit(PHP_INT_MAX);
		
		$this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
		
		$this->addCommands();
		$this->addColumns();
	}
	
	protected function addCommands()
	{
		
	}
	
	protected function addColumns()
	{
		$this->addColumn('', '', '1');
		$this->addColumn($this->lng->txt('question_pool'), '', '1');
	}
	
	public function init(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
	{
		$rows = array();
		
		foreach($sourcePoolDefinitionList as $sourcePoolDefinition)
		{
			/** @var ilTestRandomQuestionSetSourcePoolDefinition $sourcePoolDefinition */
			
			$set = array();
			
			$set['def_id'] = $sourcePoolDefinition->getId();
			$set['sequence_position'] = $sourcePoolDefinition->getSequencePosition();
			$set['source_pool_label'] = $sourcePoolDefinition->getPoolTitle();
			$set['filter_taxonomy'] = $sourcePoolDefinition->getMappedFilterTaxId();
			$set['filter_tax_node'] = $sourcePoolDefinition->getMappedFilterTaxNodeId();
			$set['question_amount'] = $sourcePoolDefinition->getQuestionAmount();
			
			$rows[] = $set;
		}
		
		$this->setData($rows);
	}
	
	public function fillRow($set)
	{
		
	}
	
}