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
class ilObjTestXMLParser extends ilSaxParser
{
    private \ILIAS\TestQuestionPool\QuestionInfoService $questioninfo;
    protected ?ilObjTest $test_obj = null;

    private ilDBInterface $db;
    private ilLogger $log;
    private ilTree $tree;
    private ilComponentRepository $component_repository;

    protected ?ilImportMapping $import_mapping = null;

    protected string $cdata = '';
    protected ?array $attr = null;

    private ?bool $in_random_question_set_config = null;
    private ?bool $in_random_question_set_settings = null;
    private ?bool $in_random_question_stage = null;
    private ?bool $in_random_question_selection_definitions = null;
    private ?ilTestRandomQuestionSetSourcePoolDefinition $source_pool_definition = null;

    public function __construct(
        ?string $path_to_file = '',
        ?bool $throw_exception = false
    ) {
        global $DIC;
        $this->db = $DIC['ilDB'];
        $this->log = $DIC['ilLog'];
        $this->tree = $DIC['tree'];
        $this->component_repository = $DIC['component.repository'];
        $this->questioninfo = $DIC->testQuestionPool()->questionInfo();
        parent::__construct($path_to_file, $throw_exception);
    }

    public function getTestOBJ(): ?\ilObjTest
    {
        return $this->test_obj;
    }

    public function setTestOBJ(\ilObjTest $test_obj): void
    {
        $this->test_obj = $test_obj;
    }

    public function getImportMapping(): ?\ilImportMapping
    {
        return $this->import_mapping;
    }

    public function setImportMapping(\ilImportMapping $import_mapping): void
    {
        $this->import_mapping = $import_mapping;
    }

    public function setHandlers($xml_parser): void
    {
        xml_set_object($xml_parser, $this);
        xml_set_element_handler($xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($xml_parser, 'handlerCharacterData');
    }

    public function handlerBeginTag(
        $xml_parser,
        string $tag_name,
        array $tag_attributes
    ): void {
        switch ($tag_name) {
            case 'RandomQuestionSetConfig':
                $this->in_random_question_set_config = true;
                break;

            case 'RandomQuestionSetSettings':
                if ($this->in_random_question_set_config !== null) {
                    $this->in_random_question_set_settings = true;
                    $this->cdata = '';
                    $this->attr = $tag_attributes;
                }
                break;

            case 'RandomQuestionStage':
                if ($this->in_random_question_set_config !== null) {
                    $this->in_random_question_stage = true;
                }
                break;

            case 'RandomQuestionStagingPool':
                if ($this->in_random_question_stage !== null) {
                    $this->cdata = '';
                    $this->attr = $tag_attributes;
                }
                break;

            case 'RandomQuestionSelectionDefinitions':
                if ($this->in_random_question_set_config !== null) {
                    $this->in_random_question_selection_definitions = true;
                }
                break;

            case 'RandomQuestionSelectionDefinition':
                if ($this->in_random_question_selection_definitions !== null) {
                    $this->source_pool_definition = new ilTestRandomQuestionSetSourcePoolDefinition($this->db, $this->test_obj);
                    ;
                    $this->attr = $tag_attributes;
                }
                break;

            case 'RandomQuestionSourcePoolTitle':
            case 'RandomQuestionSourcePoolPath':
                if ($this->source_pool_definition !== null) {
                    $this->cdata = '';
                }
                break;
        }
    }

    public function handlerEndTag($xml_parser, string $tag_name): void
    {
        switch ($tag_name) {
            case 'RandomQuestionSetConfig':
                $this->in_random_question_set_config = false;
                break;

            case 'RandomQuestionSetSettings':
                if ($this->in_random_question_set_config) {
                    $this->importRandomQuestionSetSettings($this->attr);
                    $this->attr = null;
                }
                break;

            case 'RandomQuestionStage':
                if ($this->in_random_question_set_config) {
                    $this->in_random_question_stage = false;
                }
                break;

            case 'RandomQuestionStagingPool':
                if ($this->in_random_question_set_config !== null
                    && $this->in_random_question_stage !== null) {
                    $this->importRandomQuestionStagingPool($this->attr, $this->cdata);
                    $this->attr = null;
                    $this->cdata = '';
                }
                break;

            case 'RandomQuestionSelectionDefinitions':
                if ($this->in_random_question_set_config) {
                    $this->in_random_question_selection_definitions = false;
                }
                break;

            case 'RandomQuestionSelectionDefinition':
                if ($this->in_random_question_set_config !== null
                    && $this->in_random_question_selection_definitions !== null) {
                    $this->importRandomQuestionSourcePoolDefinition($this->source_pool_definition, $this->attr);
                    $this->source_pool_definition->saveToDb();

                    $this->getImportMapping()->addMapping(
                        'components/ILIAS/Test',
                        'rnd_src_pool_def',
                        (string) $this->attr['id'],
                        (string) $this->source_pool_definition->getId()
                    );

                    $this->source_pool_definition = null;
                    $this->attr = null;
                }
                break;

            case 'RandomQuestionSourcePoolTitle':
                if ($this->source_pool_definition !== null) {
                    $this->source_pool_definition->setPoolTitle($this->cdata);
                    $this->cdata = '';
                }
                break;

            case 'RandomQuestionSourcePoolPath':
                if ($this->source_pool_definition !== null) {
                    $this->source_pool_definition->setPoolPath($this->cdata);
                    $this->cdata = '';
                }
                break;
        }
    }

    public function handlerCharacterData($xml_parser, string $char_data): void
    {
        if ($char_data != "\n") {
            // Replace multiple tabs with one space
            $char_data = preg_replace("/\t+/", " ", $char_data);

            $this->cdata .= $char_data;
        }
    }

    protected function importRandomQuestionSetSettings($attr): void
    {
        $question_set_config = new ilTestRandomQuestionSetConfig(
            $this->tree,
            $this->db,
            $this->lng,
            $this->log,
            $this->component_repository,
            $this->test_obj,
            $this->questioninfo
        );

        if (!$question_set_config->isValidQuestionAmountConfigurationMode($attr['amountMode'])) {
            throw new ilTestException(
                'invalid random test question set config amount mode given: "' . $attr['amountMode'] . '"'
            );
        }

        $question_set_config->setQuestionAmountConfigurationMode($attr['amountMode']);
        $question_set_config->setQuestionAmountPerTest((int) $attr['questAmount']);
        $question_set_config->setPoolsWithHomogeneousScoredQuestionsRequired((bool) $attr['homogeneous']);
        $question_set_config->setLastQuestionSyncTimestamp((int) $attr['synctimestamp']);

        $question_set_config->saveToDb();
    }

    protected function importRandomQuestionStagingPool(array $attr, string $cdata): void
    {
        $old_pool_id = $attr['poolId'];
        $new_pool_id = $this->db->nextId('object_data'); // yes !!

        $this->getImportMapping()->addMapping(
            'components/ILIAS/Test',
            'pool',
            (string) $old_pool_id,
            (string) $new_pool_id
        );

        $old_question_ids = explode(',', $cdata);

        foreach ($old_question_ids as $old_question_id) {
            $new_question_id = (int) $this->getImportMapping()->getMapping(
                'components/ILIAS/Test',
                'quest',
                $old_question_id
            );

            $staging_question = new ilTestRandomQuestionSetStagingPoolQuestion($this->db);
            $staging_question->setTestId($this->test_obj->getTestId());
            $staging_question->setPoolId($new_pool_id);
            $staging_question->setQuestionId($new_question_id);

            $staging_question->saveQuestionStaging();
        }
    }

    protected function importRandomQuestionSourcePoolDefinition(ilTestRandomQuestionSetSourcePoolDefinition $source_pool_definition, $attr): void
    {
        $source_pool_id = (int) $attr['poolId'];
        $effective_pool_id = (int) $this->getImportMapping()->getMapping(
            'components/ILIAS/Test',
            'pool',
            (string) $source_pool_id
        );
        $source_pool_definition->setPoolId($effective_pool_id);

        $derive_from_obj_id = true;
        // The ref_id might not be given in old export files, so we have to check for existence
        if (isset($attr['ref_id']) && is_numeric($attr['ref_id'])) {
            if ($source_pool_id === $effective_pool_id) {
                $derive_from_obj_id = false;
                $source_pool_definition->setPoolRefId((int) $attr['ref_id']);
            }
        }

        if ($derive_from_obj_id) {
            $ref_ids = ilObject::_getAllReferences($effective_pool_id);
            $ref_id = current($ref_ids);
            $source_pool_definition->setPoolRefId($ref_id ? $ref_id : null);
        }

        $source_pool_definition->setPoolQuestionCount((int) $attr['poolQuestCount']);
        $source_pool_definition->setQuestionAmount((int) $attr['questAmount']);
        $source_pool_definition->setSequencePosition((int) $attr['position']);

        if (isset($attr['typeFilter']) && strlen($attr['typeFilter']) > 0) {
            $source_pool_definition->setTypeFilterFromTypeTags(explode(',', $attr['typeFilter']));
        }

        // #21330
        if (isset($attr['tax']) && isset($attr['taxNode'])) {
            $mappedTaxFilter = [
                (int) $attr['tax'] => [
                    (int) $attr['taxNode']
                ]
            ];
            $source_pool_definition->setMappedTaxonomyFilter($mappedTaxFilter);
        } elseif (isset($attr['taxFilter']) && strlen($attr['taxFilter']) > 0) {
            $mappedTaxFilter = unserialize($attr['taxFilter']);
            $source_pool_definition->setMappedTaxonomyFilter($mappedTaxFilter);
        }
    }
}
