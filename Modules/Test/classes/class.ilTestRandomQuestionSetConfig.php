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
 * class that manages/holds the data for a question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetConfig extends ilTestQuestionSetConfig
{
    public const QUESTION_AMOUNT_CONFIG_MODE_PER_TEST = 'TEST';
    public const QUESTION_AMOUNT_CONFIG_MODE_PER_POOL = 'POOL';

    private ?bool $requirePoolsWithHomogeneousScoredQuestions = null;
    private ?string $questionAmountConfigurationMode = null;
    private ?int $questionAmountPerTest = null;
    private ?int $lastQuestionSyncTimestamp = null;
    private array $buildableMessages = [];

    public function setPoolsWithHomogeneousScoredQuestionsRequired(bool $requirePoolsWithHomogeneousScoredQuestions): void
    {
        $this->requirePoolsWithHomogeneousScoredQuestions = $requirePoolsWithHomogeneousScoredQuestions;
    }

    public function arePoolsWithHomogeneousScoredQuestionsRequired(): ?bool
    {
        return $this->requirePoolsWithHomogeneousScoredQuestions;
    }

    public function setQuestionAmountConfigurationMode(?string $questionAmountConfigurationMode): void
    {
        $this->questionAmountConfigurationMode = $questionAmountConfigurationMode;
    }

    public function getQuestionAmountConfigurationMode(): ?string
    {
        return $this->questionAmountConfigurationMode;
    }

    public function isQuestionAmountConfigurationModePerPool(): bool
    {
        return $this->getQuestionAmountConfigurationMode() == self::QUESTION_AMOUNT_CONFIG_MODE_PER_POOL;
    }

    public function isQuestionAmountConfigurationModePerTest(): bool
    {
        return $this->getQuestionAmountConfigurationMode() == self::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST;
    }

    public function isValidQuestionAmountConfigurationMode(string $amountMode): bool
    {
        switch ($amountMode) {
            case self::QUESTION_AMOUNT_CONFIG_MODE_PER_POOL:
            case self::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST:

                return true;
        }

        return false;
    }

    public function setQuestionAmountPerTest(?int $questionAmountPerTest): void
    {
        $this->questionAmountPerTest = $questionAmountPerTest;
    }

    public function getQuestionAmountPerTest(): ?int
    {
        return $this->questionAmountPerTest;
    }

    public function setLastQuestionSyncTimestamp(int $lastQuestionSyncTimestamp): void
    {
        $this->lastQuestionSyncTimestamp = $lastQuestionSyncTimestamp;
    }

    public function getLastQuestionSyncTimestamp(): ?int
    {
        return $this->lastQuestionSyncTimestamp;
    }

    public function getBuildableMessages(): array
    {
        return $this->buildableMessages;
    }

    public function initFromArray(array $data_array): void
    {
        foreach ($data_array as $field => $value) {
            switch ($field) {
                case 'req_pools_homo_scored':		$this->setPoolsWithHomogeneousScoredQuestionsRequired((bool) $value);
                    break;
                case 'quest_amount_cfg_mode':		$this->setQuestionAmountConfigurationMode($value);
                    break;
                case 'quest_amount_per_test':		$this->setQuestionAmountPerTest($value);
                    break;
                case 'quest_sync_timestamp':		$this->setLastQuestionSyncTimestamp($value);
                    break;
            }
        }
    }

    public function loadFromDb(): void
    {
        $res = $this->db->queryF(
            "SELECT * FROM tst_rnd_quest_set_cfg WHERE test_fi = %s",
            ['integer'],
            [$this->test_obj->getTestId()]
        );

        $row = $this->db->fetchAssoc($res);
        if ($row !== null) {
            $this->initFromArray($row);
        }
    }

    public function saveToDb(): void
    {
        if ($this->dbRecordExists($this->test_obj->getTestId())) {
            $this->updateDbRecord($this->test_obj->getTestId());
            return;
        }

        $this->insertDbRecord($this->test_obj->getTestId());
    }

    public function cloneToDbForTestId(int $test_id): void
    {
        $this->insertDbRecord($test_id);
    }

    public function deleteFromDb(): void
    {
        $this->db->manipulateF(
            "DELETE FROM tst_rnd_quest_set_cfg WHERE test_fi = %s",
            ['integer'],
            [$this->test_obj->getTestId()]
        );
    }

    private function dbRecordExists(int $test_id): bool
    {
        $res = $this->db->queryF(
            "SELECT COUNT(*) cnt FROM tst_rnd_quest_set_cfg WHERE test_fi = %s",
            ['integer'],
            [$test_id]
        );

        $row = $this->db->fetchAssoc($res);

        return (bool) $row['cnt'];
    }

    private function updateDbRecord(int $test_id): void
    {
        $this->db->update(
            'tst_rnd_quest_set_cfg',
            [
                'req_pools_homo_scored' => ['integer', (int) $this->arePoolsWithHomogeneousScoredQuestionsRequired()],
                'quest_amount_cfg_mode' => ['text', $this->getQuestionAmountConfigurationMode()],
                'quest_amount_per_test' => ['integer', (int) $this->getQuestionAmountPerTest()],
                'quest_sync_timestamp' => ['integer', (int) $this->getLastQuestionSyncTimestamp()]
            ],
            [
                'test_fi' => ['integer', $test_id]
            ]
        );
    }

    private function insertDbRecord(int $test_id): void
    {
        $this->db->insert(
            'tst_rnd_quest_set_cfg',
            [
                'test_fi' => ['integer', $test_id],
                'req_pools_homo_scored' => ['integer', (int) $this->arePoolsWithHomogeneousScoredQuestionsRequired()],
                'quest_amount_cfg_mode' => ['text', $this->getQuestionAmountConfigurationMode()],
                'quest_amount_per_test' => ['integer', (int) $this->getQuestionAmountPerTest()],
                'quest_sync_timestamp' => ['integer', (int) $this->getLastQuestionSyncTimestamp()]
            ]
        );
    }

    public function isQuestionSetConfigured(): bool
    {
        return $this->getLastQuestionSyncTimestamp() != 0
            && $this->isQuestionAmountConfigComplete()
            && $this->hasSourcePoolDefinitions()
            && $this->isQuestionSetBuildable();
    }

    public function isQuestionAmountConfigComplete(): bool
    {
        if ($this->isQuestionAmountConfigurationModePerPool()) {
            $sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->test_obj);

            $sourcePoolDefinitionList->loadDefinitions();

            foreach ($sourcePoolDefinitionList as $definition) {
                if ($definition->getQuestionAmount() < 1) {
                    return false;
                }
            }
        } elseif ($this->getQuestionAmountPerTest() < 1) {
            return false;
        }

        return true;
    }

    public function hasSourcePoolDefinitions(): bool
    {
        $sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->test_obj);

        return $sourcePoolDefinitionList->savedDefinitionsExist();
    }

    public function isQuestionSetBuildable(): bool
    {
        $sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->test_obj);
        $sourcePoolDefinitionList->loadDefinitions();

        $stagingPoolQuestionList = new ilTestRandomQuestionSetStagingPoolQuestionList($this->db, $this->component_repository);

        $questionSetBuilder = ilTestRandomQuestionSetBuilder::getInstance(
            $this->db,
            $this->lng,
            $this->log,
            $this->test_obj,
            $this,
            $sourcePoolDefinitionList,
            $stagingPoolQuestionList
        );

        $buildable = $questionSetBuilder->checkBuildable();
        $this->buildableMessages = $questionSetBuilder->getCheckMessages();
        return $buildable;
    }

    public function doesQuestionSetRelatedDataExist(): bool
    {
        if ($this->dbRecordExists($this->test_obj->getTestId())) {
            return true;
        }

        $sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->test_obj);

        if ($sourcePoolDefinitionList->savedDefinitionsExist()) {
            return true;
        }

        return false;
    }

    public function removeQuestionSetRelatedData(): void
    {
        $sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->test_obj);
        $sourcePoolDefinitionList->deleteDefinitions();

        $stagingPool = new ilTestRandomQuestionSetStagingPoolBuilder(
            $this->db,
            $this->log,
            $this->test_obj
        );
        $stagingPool->reset();

        $this->deleteFromDb();
    }

    public function cloneQuestionSetRelatedData(ilObjTest $clone_test_obj): void
    {
        $this->loadFromDb();
        $this->cloneToDbForTestId($clone_test_obj->getTestId());

        // clone source pool definitions (selection rules)

        $source_pool_definition_list_orig = $this->buildSourcePoolDefinitionList($this->test_obj);
        $source_pool_definition_list_orig->loadDefinitions();
        $definition_id_map = $source_pool_definition_list_orig->cloneDefinitionsForTestId($clone_test_obj->getTestId());
        $this->registerClonedSourcePoolDefinitionIdMapping($clone_test_obj, $definition_id_map);

        // build new question stage for cloned test

        $source_pool_definition_list_clone = $this->buildSourcePoolDefinitionList($clone_test_obj);
        $staging_pool = $this->buildStagingPoolBuilder($clone_test_obj);

        $source_pool_definition_list_clone->loadDefinitions();
        $staging_pool->rebuild($source_pool_definition_list_clone);
        $source_pool_definition_list_clone->saveDefinitions();

        $this->updateLastQuestionSyncTimestampForTestId($clone_test_obj->getTestId(), time());
    }

    private function registerClonedSourcePoolDefinitionIdMapping(ilObjTest $cloneTestOBJ, array $definitionIdMap): void
    {
        $cwo = ilCopyWizardOptions::_getInstance($cloneTestOBJ->getTmpCopyWizardCopyId());

        foreach ($definitionIdMap as $originalDefinitionId => $cloneDefinitionId) {
            $originalKey = $this->test_obj->getRefId() . '_rndSelDef_' . $originalDefinitionId;
            $mappedKey = $cloneTestOBJ->getRefId() . '_rndSelDef_' . $cloneDefinitionId;
            $cwo->appendMapping($originalKey, $mappedKey);
            $this->log->write(__METHOD__ . ": Added random selection definition id mapping $originalKey <-> $mappedKey");
        }
    }

    private function buildSourcePoolDefinitionList(ilObjTest $test_obj): ilTestRandomQuestionSetSourcePoolDefinitionList
    {
        return new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $this->db,
            $test_obj,
            new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
                $this->db,
                $test_obj
            )
        );
    }

    private function buildStagingPoolBuilder(ilObjTest $test_obj): ilTestRandomQuestionSetStagingPoolBuilder
    {
        $stagingPool = new ilTestRandomQuestionSetStagingPoolBuilder($this->db, $this->log, $test_obj);

        return $stagingPool;
    }

    public function updateLastQuestionSyncTimestampForTestId(int $test_id, int $timestamp): void
    {
        $this->db->update(
            'tst_rnd_quest_set_cfg',
            [
                'quest_sync_timestamp' => ['integer', (int) $timestamp]
            ],
            [
                'test_fi' => ['integer', $test_id]
            ]
        );
    }

    public function isResultTaxonomyFilterSupported(): bool
    {
        return true;
    }

    public function getSelectableQuestionPools(): array
    {
        return $this->test_obj->getAvailableQuestionpools(
            true,
            $this->arePoolsWithHomogeneousScoredQuestionsRequired(),
            false,
            true,
            true
        );
    }

    public function doesSelectableQuestionPoolsExist(): bool
    {
        return (bool) count($this->getSelectableQuestionPools());
    }

    public function areDepenciesBroken(): bool
    {
        return $this->test_obj->isTestFinalBroken();
    }

    public function getDepenciesBrokenMessage(ilLanguage $lng): string
    {
        return $lng->txt('tst_old_style_rnd_quest_set_broken');
    }

    public function isValidRequestOnBrokenQuestionSetDepencies(string $next_class, string $cmd): bool
    {
        switch ($next_class) {
            case 'ilobjectmetadatagui':
            case 'ilpermissiongui':

                return true;

            case 'ilobjtestgui':
            case '':

                $cmds = [
                    'infoScreen', 'participants', 'npSetFilter', 'npResetFilter',
                ];

                if (in_array($cmd, $cmds)) {
                    return true;
                }

                break;
        }

        return false;
    }

    public function getHiddenTabsOnBrokenDepencies(): array
    {
        return [
            'assQuestions', 'settings', 'manscoring', 'scoringadjust', 'statistics', 'history', 'export'
        ];
    }

    public function getCommaSeparatedSourceQuestionPoolLinks(): string
    {
        $definitionList = $this->buildSourcePoolDefinitionList($this->test_obj);
        $definitionList->loadDefinitions();

        $poolTitles = [];

        foreach ($definitionList as $definition) {
            $refId = current(ilObject::_getAllReferences($definition->getPoolId()));
            $href = ilLink::_getLink($refId, 'qpl');
            $title = $definition->getPoolTitle();

            $poolTitles[$definition->getPoolId()] = "<a href=\"$href\" alt=\"$title\">$title</a>";
        }

        return implode(', ', $poolTitles);
    }
}
