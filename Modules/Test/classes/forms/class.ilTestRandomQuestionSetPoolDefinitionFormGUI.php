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
 * GUI class for random question set pool config form
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/Test
 *
 * @ilCtrl_Calls ilTestRandomQuestionSetPoolDefinitionFormGUI: ilFormPropertyDispatchGUI
 */
class ilTestRandomQuestionSetPoolDefinitionFormGUI extends ilPropertyFormGUI
{
    public ilObjTest $testOBJ ;

    public ilTestRandomQuestionSetConfigGUI $questionSetConfigGUI;

    public ilTestRandomQuestionSetConfig $questionSetConfig;

    private ?string $saveCommand = null;

    private ?string $saveAndNewCommand = null;

    public function __construct(ilCtrlInterface $ctrl, ilLanguage $lng, ilObjTest $testOBJ, ilTestRandomQuestionSetConfigGUI $questionSetConfigGUI, ilTestRandomQuestionSetConfig $questionSetConfig)
    {
        parent::__construct();

        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->testOBJ = $testOBJ;
        $this->questionSetConfigGUI = $questionSetConfigGUI;
        $this->questionSetConfig = $questionSetConfig;
    }

    public function setSaveCommand(string $saveCommand): void
    {
        $this->saveCommand = $saveCommand;
    }

    public function getSaveCommand(): ?string
    {
        return $this->saveCommand;
    }

    public function setSaveAndNewCommand(string $saveAndNewCommand): void
    {
        $this->saveAndNewCommand = $saveAndNewCommand;
    }

    public function getSaveAndNewCommand(): ?string
    {
        return $this->saveAndNewCommand;
    }

    public function build(ilTestRandomQuestionSetSourcePoolDefinition $sourcePool, $availableTaxonomyIds): void
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
        $hiddenDefId->setValue((string) $sourcePool->getId());
        $this->addItem($hiddenDefId);

        $hiddenPoolId = new ilHiddenInputGUI('quest_pool_id');
        $hiddenPoolId->setValue((string) $sourcePool->getPoolId());
        $this->addItem($hiddenPoolId);

        $nonEditablePoolLabel = new ilNonEditableValueGUI(
            $this->lng->txt('tst_inp_source_pool_label'),
            'quest_pool_label'
        );
        $nonEditablePoolLabel->setValue($sourcePool->getPoolInfoLabel($this->lng));

        $this->addItem($nonEditablePoolLabel);


        if (count($availableTaxonomyIds)) {
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
        // fau.
        } else {
            $hiddenNoTax = new ilHiddenInputGUI('filter_tax');
            $hiddenNoTax->setValue('0');
            $this->addItem($hiddenNoTax);

            $nonEditableNoTax = new ilNonEditableValueGUI(
                $this->lng->txt('tst_inp_source_pool_filter_tax'),
                'no_tax_label'
            );
            $nonEditableNoTax->setValue($this->lng->txt('tst_inp_no_available_tax_hint'));
            $this->addItem($nonEditableNoTax);
        }

        $lifecycleFilterValues = $sourcePool->getLifecycleFilter();
        $lifecycleCheckbox = new ilCheckboxInputGUI($this->lng->txt('tst_filter_lifecycle_enabled'), 'filter_lifecycle_enabled');
        $lifecycleCheckbox->setChecked(!empty($lifecycleFilterValues));
        $lifecycleFilter = new ilSelectInputGUI($this->lng->txt('qst_lifecycle'), 'filter_lifecycle');
        $lifecycleFilter->setRequired(true);
        $lifecycleFilter->setMulti(true);
        $lifecycleFilter->setOptions(ilAssQuestionLifecycle::getDraftInstance()->getSelectOptions($this->lng));
        $lifecycleFilter->setValue($lifecycleFilterValues);
        $lifecycleCheckbox->addSubItem($lifecycleFilter);
        $this->addItem($lifecycleCheckbox);

        // fau: taxFilter/typeFilter - show type filter selection
        $typeFilterOptions = array();
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
                $questionAmountPerSourcePool->setValue(
                    $sourcePool->getQuestionAmount() ? (string) $sourcePool->getQuestionAmount() : null
                );
            }

            $this->addItem($questionAmountPerSourcePool);
        }
    }

    public function applySubmit(ilTestRandomQuestionSetSourcePoolDefinition $sourcePoolDefinition, $availableTaxonomyIds): void
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

        $filter = array();
        if ($this->getItemByPostVar("filter_lifecycle_enabled")->getChecked()) {
            $filter = $this->getItemByPostVar("filter_lifecycle")->getMultiValues();
        }
        $sourcePoolDefinition->setLifecycleFilter($filter);

        // fau.

        if ($this->questionSetConfig->isQuestionAmountConfigurationModePerPool()) {
            $sourcePoolDefinition->setQuestionAmount($this->getItemByPostVar('question_amount_per_pool')->getValue());
        }
    }
}
