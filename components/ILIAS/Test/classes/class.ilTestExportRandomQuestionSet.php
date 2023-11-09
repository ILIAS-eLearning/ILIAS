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

declare(strict_types=1);

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestExportRandomQuestionSet extends ilTestExport
{
    protected ilTestRandomQuestionSetSourcePoolDefinitionList $src_pool_def_list;

    /**
     * @var array<ilTestRandomQuestionSetStagingPoolQuestionList>
     */
    protected $staging_pool_question_list_by_pool_id;

    public function __construct(
        \ilObjTest $test_obj,
        private ilLanguage $lng,
        private ilLogger $logger,
        private ilTree $tree,
        private ilComponentRepository $component_repository,
        protected \ILIAS\TestQuestionPool\QuestionInfoService $questioninfo,
        string $mode = "xml"

    ) {
        parent::__construct($test_obj, $mode);
    }

    protected function initXmlExport(): void
    {
        $src_pool_def_factory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
            $this->db,
            $this->test_obj
        );

        $this->src_pool_def_list = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $this->db,
            $this->test_obj,
            $src_pool_def_factory
        );

        $this->src_pool_def_list->loadDefinitions();

        $this->test_obj->questions = $this->getQuestionIds();
        $this->staging_pool_question_list_by_pool_id = [];
    }

    protected function populateQuestionSetConfigXml(ilXmlWriter $xml_writer): void
    {
        $xml_writer->xmlStartTag('RandomQuestionSetConfig');
        $this->populateCommonSettings($xml_writer);
        $this->populateQuestionStages($xml_writer);
        $this->populateSelectionDefinitions($xml_writer);
        $xml_writer->xmlEndTag('RandomQuestionSetConfig');
    }

    protected function populateCommonSettings(ilXmlWriter $xml_writer)
    {
        $question_set_config = new ilTestRandomQuestionSetConfig(
            $this->tree,
            $this->db,
            $this->lng,
            $this->logger,
            $this->component_repository,
            $this->test_obj,
            $this->questioninfo
        );
        $question_set_config->loadFromDb();

        $xml_writer->xmlElement('RandomQuestionSetSettings', [
            'amountMode' => $question_set_config->getQuestionAmountConfigurationMode(),
            'questAmount' => $question_set_config->getQuestionAmountPerTest(),
            'homogeneous' => $question_set_config->arePoolsWithHomogeneousScoredQuestionsRequired(),
            'synctimestamp' => $question_set_config->getLastQuestionSyncTimestamp()
        ]);
    }

    protected function populateQuestionStages(ilXmlWriter $xml_writer)
    {
        $xml_writer->xmlStartTag('RandomQuestionStage');

        foreach ($this->src_pool_def_list->getInvolvedSourcePoolIds() as $pool_id) {
            $question_list = $this->getLoadedStagingPoolQuestionList($pool_id);

            $xml_writer->xmlStartTag('RandomQuestionStagingPool', ['poolId' => $pool_id]);
            $xml_writer->xmlData(implode(',', $question_list->getQuestions()));
            $xml_writer->xmlEndTag('RandomQuestionStagingPool');
        }

        $xml_writer->xmlEndTag('RandomQuestionStage');
    }

    protected function populateSelectionDefinitions(ilXmlWriter $xml_writer)
    {
        $xml_writer->xmlStartTag('RandomQuestionSelectionDefinitions');

        foreach ($this->src_pool_def_list as $definition) {
            $attributes = [
                'id' => $definition->getId(),
                'ref_id' => $definition->getPoolRefId(),
                'poolId' => $definition->getPoolId(),
                'questAmount' => $definition->getQuestionAmount() ?? '',
                'poolQuestCount' => $definition->getPoolQuestionCount(),
                'position' => $definition->getSequencePosition(),
                'typeFilter' => implode(',', $definition->getTypeFilterAsTypeTags()),
            ];


            // #21330
            $mappedTaxFilter = $definition->getMappedTaxonomyFilter();
            if (is_array($mappedTaxFilter) && count($mappedTaxFilter) > 0) {
                $attributes['taxFilter'] = serialize($mappedTaxFilter);
            }

            $xml_writer->xmlStartTag('RandomQuestionSelectionDefinition', $attributes);
            $xml_writer->xmlElement('RandomQuestionSourcePoolTitle', null, $definition->getPoolTitle());
            $xml_writer->xmlElement('RandomQuestionSourcePoolPath', null, $definition->getPoolPath());
            $xml_writer->xmlEndTag('RandomQuestionSelectionDefinition');
        }

        $xml_writer->xmlEndTag('RandomQuestionSelectionDefinitions');
    }

    protected function getQuestionsQtiXml(): string
    {
        $question_qti_xml = '';

        foreach ($this->src_pool_def_list->getInvolvedSourcePoolIds() as $pool_id) {
            $question_list = $this->getLoadedStagingPoolQuestionList($pool_id);

            foreach ($question_list as $question_id) {
                $question_qti_xml .= $this->getQuestionQtiXml($question_id);
            }
        }

        return $question_qti_xml;
    }

    /**
     * @return array
     */
    protected function getQuestionIds(): array
    {
        $question_ids = [];

        foreach ($this->src_pool_def_list->getInvolvedSourcePoolIds() as $pool_id) {
            $question_list = $this->getLoadedStagingPoolQuestionList($pool_id);

            foreach ($question_list as $question_id) {
                $question_ids[] = $question_id;
            }
        }

        return $question_ids;
    }

    protected function getLoadedStagingPoolQuestionList(int $pool_id): ilTestRandomQuestionSetStagingPoolQuestionList
    {
        if (!isset($this->staging_pool_question_list_by_pool_id[$pool_id])) {
            $question_list = new ilTestRandomQuestionSetStagingPoolQuestionList($this->db, $this->component_repository);
            $question_list->setTestId($this->test_obj->getTestId());
            $question_list->setPoolId($pool_id);
            $question_list->loadQuestions();

            $this->staging_pool_question_list_by_pool_id[$pool_id] = $question_list;
        }

        return $this->staging_pool_question_list_by_pool_id[$pool_id];
    }
}
