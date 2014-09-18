<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Modules/Course/exceptions/class.ilLOInvalidConfiguationException.php';

/**
* Class ilLOTestAssignmentTableGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de> 
* $Id$
*
*
*/
class ilLOTestAssignmentTableGUI extends ilTable2GUI
{
	private $test_type = 0;
	private $settings = NULL;
	
	
	/**
	 * Constructor
	 * @param ilObject $a_parent_obj
	 * @param type $a_parent_cmd
	 * @param type $a_test_type
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_container_id, $a_test_type)
	{
		$this->test_type = $a_test_type;
		$this->setId('obj_loc_'.$a_container_id);
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->settings = ilLOSettings::getInstanceByObjId($a_container_id);
	}
	
	/**
	 * Get settings
	 * @return ilLOSettings
	 */
	public function getSettings()
	{
		return $this->settings;
	}
	
	/**
	 * Init table
	 */
	public function init()
	{
		$this->addColumn('','', '20px');
		$this->addColumn($this->lng->txt('title'),'title');
		$this->addColumn($this->lng->txt('crs_loc_tbl_tst_type'),'ttype');
		$this->addColumn($this->lng->txt('crs_loc_tbl_tst_qst_qpl'),'qstqpl');
		
		$this->addMultiCommand('confirmDeleteTest', $this->lng->txt('crs_loc_delete_assignment'));
			 
		$this->setRowTemplate("tpl.crs_loc_tst_row.html","Modules/Course");
		$this->setFormAction($GLOBALS['ilCtrl']->getFormAction($this->getParentObject()));
		
		$this->setDefaultOrderField('title');
		$this->setDefaultOrderDirection('asc');
	}
	
	/**
	 * 
	 * @param type $set
	 */
	public function fillRow($set)
	{
		global $ilCtrl;
		
		$this->tpl->setVariable('VAL_ID',$set['ref_id']);
		$this->tpl->setVariable('VAL_TITLE',$set['title']);
		include_once './Services/Link/classes/class.ilLink.php';
		
		$ilCtrl->setParameterByClass('ilobjtestgui','ref_id',$set['ref_id']);
		$ilCtrl->setParameterByClass('ilobjtestgui','cmd','questionsTabGateway');
		$this->tpl->setVariable(
				'TITLE_LINK',
				$ilCtrl->getLinkTargetByClass('ilobjtestgui')
		);
				
		
		
		#$this->tpl->setVariable('TITLE_LINK',ilLink::_getLink($set['ref_id']));
		if(strlen($set['description']))
		{
			$this->tpl->setVariable('VAL_DESC',$set['description']);
		}

		switch($set['ttype'])
		{
			case ilObjTest::QUESTION_SET_TYPE_FIXED:
				$type = $this->lng->txt('tst_question_set_type_fixed');
				break;
			
			case ilObjTest::QUESTION_SET_TYPE_RANDOM:
				$type = $this->lng->txt('tst_question_set_type_random');
				break;
		}
		
		$this->tpl->setVariable('VAL_TTYPE',$type);
		$this->tpl->setVariable('VAL_QST_QPL',$set['qst_info']);
		
		if(count($set['qpls']))
		{
			foreach($set['qpls'] as $title)
			{
				$this->tpl->setCurrentBlock('qpl');
				$this->tpl->setVariable('MAT_TITLE',$title);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->touchBlock('ul_begin');
			$this->tpl->touchBlock('ul_end');
		}
	}
	
	/**
	 * Parse test
	 * throws ilLOInvalidConfigurationException in case assigned test cannot be found.
	 */
	public function parse($a_tst_ref_id)
	{
		include_once './Modules/Test/classes/class.ilObjTest.php';
		$tst = ilObjectFactory::getInstanceByRefId($a_tst_ref_id,false);
		
		if(!$tst instanceof ilObjTest)
		{
			throw new ilLOInvalidConfigurationException('No valid test given');
		}
		$tst_data['ref_id'] = $tst->getRefId();
		$tst_data['title'] = $tst->getTitle();
		$tst_data['description'] = $tst->getLongDescription();
		$tst_data['ttype'] = $tst->getQuestionSetType();
		
		switch($tst->getQuestionSetType())
		{
			case ilObjTest::QUESTION_SET_TYPE_FIXED:
				$tst_data['qst_info'] = $this->lng->txt('crs_loc_tst_num_qst');
				$tst_data['qst_info'] .= (' ' . count($tst->getAllQuestions()));
				break;
			
			default:
				// get available assiged question pools
				include_once './Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
				include_once './Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
				
				$list = new ilTestRandomQuestionSetSourcePoolDefinitionList(
						$GLOBALS['ilDB'],
						$tst,
						new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
								$GLOBALS['ilDB'],
								$tst
						)
				);
				
				$list->loadDefinitions();
				
				// tax translations
				include_once './Modules/Test/classes/class.ilTestTaxonomyFilterLabelTranslater.php';
				$translater = new ilTestTaxonomyFilterLabelTranslater($GLOBALS['ilDB']);
				$translater->loadLabels($list);
				
				$tst_data['qst_info'] = $this->lng->txt('crs_loc_tst_qpls');
				$num = 0;
				foreach ($list as $definition)
				{
					/** @var ilTestRandomQuestionSetSourcePoolDefinition[] $definition */
					$title = $definition->getPoolTitle();
					$tax_id = $definition->getMappedFilterTaxId();
					if($tax_id)
					{
						$title .= (' -> '. $translater->getTaxonomyTreeLabel($tax_id));
					}
					$tax_node = $definition->getMappedFilterTaxNodeId();
					if($tax_node)
					{
						$title .= (' -> ' .$translater->getTaxonomyNodeLabel($tax_node));
					}
					$tst_data['qpls'][] = $title;
					++$num;
				}
				if(!$num)
				{
					$tst_data['qst_info'] .= (' '. (int) 0);
				}
				break;
		}
		
		
		$this->setData(array($tst_data));
	}
}
?>