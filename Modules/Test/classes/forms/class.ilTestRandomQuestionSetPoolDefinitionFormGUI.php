<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * GUI class for random question set pool config form
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 * 
 * @ilCtrl_Calls ilTestRandomQuestionSetPoolDefinitionFormGUI: ilFormPropertyDispatchGUI
 */
class ilTestRandomQuestionSetPoolDefinitionFormGUI extends ilPropertyFormGUI
{
	/**
	 * global $ilCtrl object
	 * 
	 * @var ilCtrl
	 */
	public $ctrl = null;
	
	/**
	 * global $lng object
	 * 
	 * @var ilLanguage
	 */
	public $lng = null;
	
	/**
	 * object instance for current test
	 *
	 * @var ilObjTest
	 */
	public $testOBJ = null;
	
	/**
	 * global $lng object
	 * 
	 * @var ilTestRandomQuestionSetConfigGUI
	 */
	public $questionSetConfigGUI = null;
	
	/**
	 * global $lng object
	 * 
	 * @var ilTestRandomQuestionSetConfig
	 */
	public $questionSetConfig = null;

	private $saveCommand = null;
	
	public function __construct(ilCtrl $ctrl, ilLanguage $lng, ilObjTest $testOBJ, ilTestRandomQuestionSetConfigGUI $questionSetConfigGUI, ilTestRandomQuestionSetConfig $questionSetConfig)
	{
		$this->ctrl = $ctrl;
		$this->lng = $lng;
		$this->testOBJ = $testOBJ;
		$this->questionSetConfigGUI = $questionSetConfigGUI;
		$this->questionSetConfig = $questionSetConfig;
	}

	public function setSaveCommand($saveCommand)
	{
		$this->saveCommand = $saveCommand;
	}

	public function getSaveCommand()
	{
		return $this->saveCommand;
	}

	public function build(ilTestRandomQuestionSetSourcePoolDefinition $sourcePool, $availableTaxonomyIds)
	{
		$this->setFormAction( $this->ctrl->getFormAction($this->questionSetConfigGUI) );
		
		$this->setTitle( $this->lng->txt('tst_rnd_quest_set_cfg_pool_form') );
		$this->setId('tstRndQuestSetCfgPoolForm');
		
		$this->addCommandButton(
				$this->getSaveCommand(), $this->lng->txt('save')
		);
		$this->addCommandButton(
				ilTestRandomQuestionSetConfigGUI::CMD_SHOW_SRC_POOL_DEF_LIST, $this->lng->txt('cancel')
		);

		$hiddenDefId = new ilHiddenInputGUI('src_pool_def_id');
		$hiddenDefId->setValue( $sourcePool->getId() );
		$this->addItem($hiddenDefId);

		$hiddenPoolId = new ilHiddenInputGUI('quest_pool_id');
		$hiddenPoolId->setValue( $sourcePool->getPoolId() );
		$this->addItem($hiddenPoolId);

		$nonEditablePoolLabel = new ilNonEditableValueGUI(
				$this->lng->txt('tst_inp_source_pool_label'), 'quest_pool_label'
		);
		$nonEditablePoolLabel->setValue( $sourcePool->getPoolInfoLabel($this->lng) );

		$this->addItem($nonEditablePoolLabel);
		
		
		if( count($availableTaxonomyIds) )
		{
			$taxRadio = new ilRadioGroupInputGUI(
					$this->lng->txt('tst_inp_source_pool_filter_tax'), 'filter_tax'
			);
			
			$taxRadio->setRequired(true);
			
			$taxRadio->addOption(new ilRadioOption(
					$this->lng->txt('tst_inp_source_pool_no_tax_filter'), 0,
					$this->lng->txt('tst_inp_source_pool_no_tax_filter_info')
			));
			
			$taxRadio->setValue(0);
			
			require_once 'Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php';

			foreach($availableTaxonomyIds as $taxId)
			{
				$taxonomy = new ilObjTaxonomy($taxId);
				$label = sprintf($this->lng->txt('tst_inp_source_pool_filter_tax_x'), $taxonomy->getTitle());
				
				$taxRadioOption = new ilRadioOption(
					$label, $taxId, $this->lng->txt('tst_inp_source_pool_filter_tax_x_info')
				);
				
				$taxRadio->addOption($taxRadioOption);
				
				$taxSelect = new ilTaxSelectInputGUI($taxId, "filter_tax_$taxId", false);
				$taxSelect->setRequired(true);
				$taxRadioOption->addSubItem($taxSelect);
				
				if( $taxId == $sourcePool->getFilterTaxId() )
				{
					$taxRadio->setValue( $sourcePool->getFilterTaxId() );
					$taxSelect->setValue( $sourcePool->getFilterTaxNodeId() );
				}
			}
			
			$this->addItem($taxRadio);
		}
		
		
		if( $this->questionSetConfig->isQuestionAmountConfigurationModePerPool() )
		{
			$questionAmountPerSourcePool = new ilNumberInputGUI(
					$this->lng->txt('tst_inp_quest_amount_per_source_pool'), 'question_amount_per_pool'
			);
			
			$questionAmountPerSourcePool->setRequired(true);
			$questionAmountPerSourcePool->allowDecimals(false);
			$questionAmountPerSourcePool->setMinValue(0);
			$questionAmountPerSourcePool->setMinvalueShouldBeGreater(true);
			
			if( $sourcePool->getQuestionAmount() )
			{
				$questionAmountPerSourcePool->setValue( $sourcePool->getQuestionAmount() );
			}
			
			$this->addItem($questionAmountPerSourcePool);
		}
	}
	
	public function applySubmit(ilTestRandomQuestionSetSourcePoolDefinition $sourcePoolDefinition, $availableTaxonomyIds)
	{
		switch( true )
		{
			case $this->getItemByPostVar('source_pool_filter_tax') === null:
				
			case !in_array($this->getItemByPostVar('filter_tax')->getValue(), $availableTaxonomyIds):

				$sourcePoolDefinition->setFilterTaxId(null);
				$sourcePoolDefinition->setFilterTaxNodeId(null);
				break;
			
			default:
				
				$taxId = $this->getItemByPostVar('filter_tax')->getValue();

				$sourcePoolDefinition->setFilterTaxId( $taxId );

				$sourcePoolDefinition->setFilterTaxNodeId( $this->getItemByPostVar("filter_tax_$taxId")->getValue() );
		}

		$sourcePoolDefinition->setQuestionAmount( $this->getItemByPostVar('question_amount_per_pool')->getValue() );
	}
}
