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

    /**
     * @var null|string
     */
    private $saveAndNewCommand = null;
    
    public function __construct(ilCtrl $ctrl, ilLanguage $lng, ilObjTest $testOBJ, ilTestRandomQuestionSetConfigGUI $questionSetConfigGUI, ilTestRandomQuestionSetConfig $questionSetConfig)
    {
        parent::__construct();

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

    /**
     * @param null|string $saveAndNewCommand
     */
    public function setSaveAndNewCommand($saveAndNewCommand)
    {
        $this->saveAndNewCommand = $saveAndNewCommand;
    }

    /**
     * @return null|string
     */
    public function getSaveAndNewCommand()
    {
        return $this->saveAndNewCommand;
    }
    
    public function build(ilTestRandomQuestionSetSourcePoolDefinition $sourcePool, $availableTaxonomyIds)
    {
        $this->setFormAction($this->ctrl->getFormAction($this->questionSetConfigGUI));
        
        $this->setTitle($this->lng->txt('tst_rnd_quest_set_cfg_pool_form'));
        $this->setId('tstRndQuestSetCfgPoolForm');
        
        $this->addCommandButton(
            $this->getSaveCommand(),
            $this->lng->txt('save_and_back')
        );

        if (null !== $this->getSaveAndNewCommand()) {
            $this->addCommandButton(
                $this->getSaveAndNewCommand(),
                $this->lng->txt('tst_save_and_create_new_rule')
            );
        }
        
        $this->addCommandButton(
            ilTestRandomQuestionSetConfigGUI::CMD_SHOW_SRC_POOL_DEF_LIST,
            $this->lng->txt('cancel')
        );

        $hiddenDefId = new ilHiddenInputGUI('src_pool_def_id');
        $hiddenDefId->setValue($sourcePool->getId());
        $this->addItem($hiddenDefId);

        $hiddenPoolId = new ilHiddenInputGUI('quest_pool_id');
        $hiddenPoolId->setValue($sourcePool->getPoolId());
        $this->addItem($hiddenPoolId);

        $nonEditablePoolLabel = new ilNonEditableValueGUI(
            $this->lng->txt('tst_inp_source_pool_label'),
            'quest_pool_label'
        );
        $nonEditablePoolLabel->setValue($sourcePool->getPoolInfoLabel($this->lng));

        $this->addItem($nonEditablePoolLabel);
        
        
        if (count($availableTaxonomyIds)) {
            // fau: taxFilter/typeFilter  - edit multiple taxonomy and node selections
            require_once 'Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php';
            
            // this is needed by ilTaxSelectInputGUI in some cases
            require_once 'Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php';
            ilOverlayGUI::initJavaScript();
            
            $filter = $sourcePool->getOriginalTaxonomyFilter();
            foreach ($availableTaxonomyIds as $taxId) {
                $taxonomy = new ilObjTaxonomy($taxId);
                $taxLabel = sprintf($this->lng->txt('tst_inp_source_pool_filter_tax_x'), $taxonomy->getTitle());
                
                $taxCheckbox = new ilCheckboxInputGUI($taxLabel, "filter_tax_id_$taxId");
                
                
                
                $this->ctrl->setParameterByClass('iltaxselectinputgui', 'src_pool_def_id', $sourcePool->getId());
                $this->ctrl->setParameterByClass('iltaxselectinputgui', 'quest_pool_id', $sourcePool->getPoolId());
                $taxSelect = new ilTaxSelectInputGUI($taxId, "filter_tax_nodes_$taxId", true);
                $taxSelect->setRequired(true);
                
                if (isset($filter[$taxId])) {
                    $taxCheckbox->setChecked(true);
                    $taxSelect->setValue($filter[$taxId]);
                }
                $taxCheckbox->addSubItem($taxSelect);
                $this->addItem($taxCheckbox);
            }
            
            #$taxRadio = new ilRadioGroupInputGUI(
            #		$this->lng->txt('tst_inp_source_pool_filter_tax'), 'filter_tax'
            #);
            
            #$taxRadio->setRequired(true);
            
            #$taxRadio->addOption(new ilRadioOption(
            #		$this->lng->txt('tst_inp_source_pool_no_tax_filter'), 0
            #));
            
            #$taxRadio->setValue(0);
            
            #require_once 'Services/Taxonomy/classes/class.ilTaxSelectInputGUI.php';
            
            #foreach($availableTaxonomyIds as $taxId)
            #{
            #	$taxonomy = new ilObjTaxonomy($taxId);
            #	$label = sprintf($this->lng->txt('tst_inp_source_pool_filter_tax_x'), $taxonomy->getTitle());
            
            #	$taxRadioOption = new ilRadioOption($label, $taxId);
            
            #	$taxRadio->addOption($taxRadioOption);
            
            #	$taxSelect = new ilTaxSelectInputGUI($taxId, "filter_tax_$taxId", false);
            #	$taxSelect->setRequired(true);
            #	$taxRadioOption->addSubItem($taxSelect);
            
            #	if( $taxId == $sourcePool->getOriginalFilterTaxId() )
            #	{
            #		$taxRadio->setValue( $sourcePool->getOriginalFilterTaxId() );
            #		$taxSelect->setValue( $sourcePool->getOriginalFilterTaxNodeId() );
            #	}
            #}
            
            #$this->addItem($taxRadio);
            // fau.
        } else {
            $hiddenNoTax = new ilHiddenInputGUI('filter_tax');
            $hiddenNoTax->setValue(0);
            $this->addItem($hiddenNoTax);

            $nonEditableNoTax = new ilNonEditableValueGUI(
                $this->lng->txt('tst_inp_source_pool_filter_tax'),
                'no_tax_label'
            );
            $nonEditableNoTax->setValue($this->lng->txt('tst_inp_no_available_tax_hint'));
            $this->addItem($nonEditableNoTax);
        }
        
        // fau: taxFilter/typeFilter - show type filter selection
        $typeFilterOptions = array();
        require_once("./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php");
        foreach (ilObjQuestionPool::_getQuestionTypes(true) as $translation => $data) {
            $typeFilterOptions[$data['question_type_id']] = $translation;
        }
        $filterIds = $sourcePool->getTypeFilter();
        $typeCheckbox = new ilCheckboxInputGUI($this->lng->txt('tst_filter_question_type_enabled'), 'filter_type_enabled');
        $typeCheckbox->setChecked(!empty($filterIds));
        $typeFilter = new ilSelectInputGUI($this->lng->txt('tst_filter_question_type'), 'filter_type');
        $typeFilter->setRequired(true);
        $typeFilter->setMulti(true);
        $typeFilter->setOptions($typeFilterOptions);
        $typeFilter->setValue($filterIds);
        $typeCheckbox->addSubItem($typeFilter);
        $this->addItem($typeCheckbox);
        // fau.
        
        if ($this->questionSetConfig->isQuestionAmountConfigurationModePerPool()) {
            $questionAmountPerSourcePool = new ilNumberInputGUI(
                $this->lng->txt('tst_inp_quest_amount_per_source_pool'),
                'question_amount_per_pool'
            );
            
            $questionAmountPerSourcePool->setRequired(true);
            $questionAmountPerSourcePool->allowDecimals(false);
            $questionAmountPerSourcePool->setMinValue(0);
            $questionAmountPerSourcePool->setMinvalueShouldBeGreater(true);
            $questionAmountPerSourcePool->setSize(4);
            
            if ($sourcePool->getQuestionAmount()) {
                $questionAmountPerSourcePool->setValue($sourcePool->getQuestionAmount());
            }
            
            $this->addItem($questionAmountPerSourcePool);
        }
    }
    
    public function applySubmit(ilTestRandomQuestionSetSourcePoolDefinition $sourcePoolDefinition, $availableTaxonomyIds)
    {
        // fau: taxFilter/typeFilter - submit multiple taxonomy and node selections - submit type selections
        $filter = array();
        foreach ($availableTaxonomyIds as $taxId) {
            if ($this->getItemByPostVar("filter_tax_id_$taxId")->getChecked()) {
                $nodeIds = (array) $this->getItemByPostVar("filter_tax_nodes_$taxId")->getValue();
                if (!empty($nodeIds)) {
                    foreach ($nodeIds as $nodeId) {
                        $filter[(int) $taxId][] = (int) $nodeId;
                    }
                }
            }
        }
        $sourcePoolDefinition->setOriginalTaxonomyFilter($filter);
        
        #switch( true )
        #{
        #	case $this->getItemByPostVar('source_pool_filter_tax') === null:
        
        #	case !in_array($this->getItemByPostVar('filter_tax')->getValue(), $availableTaxonomyIds):
        
        #		$sourcePoolDefinition->setOriginalFilterTaxId(null);
        #		$sourcePoolDefinition->setOriginalFilterTaxNodeId(null);
        #		break;
        
        #	default:
        
        #		$taxId = $this->getItemByPostVar('filter_tax')->getValue();
        
        #		$sourcePoolDefinition->setOriginalFilterTaxId( $taxId );
        
        #		$sourcePoolDefinition->setOriginalFilterTaxNodeId( $this->getItemByPostVar("filter_tax_$taxId")->getValue() );
        #}

        $filter = array();
        if ($this->getItemByPostVar("filter_type_enabled")->getChecked()) {
            $filter = $this->getItemByPostVar("filter_type")->getMultiValues();
        }
        $sourcePoolDefinition->setTypeFilter($filter);
        // fau.
        
        if ($this->questionSetConfig->isQuestionAmountConfigurationModePerPool()) {
            $sourcePoolDefinition->setQuestionAmount($this->getItemByPostVar('question_amount_per_pool')->getValue());
        }
    }
}
