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
 * class that manages/holds the data for a question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetConfig extends ilTestQuestionSetConfig
{
    const QUESTION_AMOUNT_CONFIG_MODE_PER_TEST = 'TEST';
    const QUESTION_AMOUNT_CONFIG_MODE_PER_POOL = 'POOL';
    
    /**
     * @var boolean
     */
    private $requirePoolsWithHomogeneousScoredQuestions = null;
    
    /**
     * @var string
     */
    private $questionAmountConfigurationMode = null;
    
    /**
     * @var integer
     */
    private $questionAmountPerTest = null;
    
    /**
     * @var integer
     */
    private $lastQuestionSyncTimestamp = null;
    
    //fau: fixRandomTestBuildable - variable for messages
    private $buildableMessages = array();
    // fau.

    /**
     * @param boolean $requirePoolsWithHomogeneousScoredQuestions
     */
    public function setPoolsWithHomogeneousScoredQuestionsRequired($requirePoolsWithHomogeneousScoredQuestions)
    {
        $this->requirePoolsWithHomogeneousScoredQuestions = $requirePoolsWithHomogeneousScoredQuestions;
    }
    
    /**
     * @return boolean
     */
    public function arePoolsWithHomogeneousScoredQuestionsRequired() : ?bool
    {
        return $this->requirePoolsWithHomogeneousScoredQuestions;
    }
    
    /**
     * @param string $questionAmountConfigurationMode
     */
    public function setQuestionAmountConfigurationMode($questionAmountConfigurationMode)
    {
        $this->questionAmountConfigurationMode = $questionAmountConfigurationMode;
    }
    
    /**
     * @return string
     */
    public function getQuestionAmountConfigurationMode() : ?string
    {
        return $this->questionAmountConfigurationMode;
    }
    
    /**
     * @return boolean
     */
    public function isQuestionAmountConfigurationModePerPool() : bool
    {
        return $this->getQuestionAmountConfigurationMode() == self::QUESTION_AMOUNT_CONFIG_MODE_PER_POOL;
    }
    
    /**
     * @return boolean
     */
    public function isQuestionAmountConfigurationModePerTest() : bool
    {
        return $this->getQuestionAmountConfigurationMode() == self::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST;
    }
    
    public function isValidQuestionAmountConfigurationMode($amountMode) : bool
    {
        switch ($amountMode) {
            case self::QUESTION_AMOUNT_CONFIG_MODE_PER_POOL:
            case self::QUESTION_AMOUNT_CONFIG_MODE_PER_TEST:
                
                return true;
        }
        
        return false;
    }
    
    /**
     * @param integer $questionAmountPerTest
     */
    public function setQuestionAmountPerTest($questionAmountPerTest)
    {
        $this->questionAmountPerTest = $questionAmountPerTest;
    }
    
    /**
     * @return integer
     */
    public function getQuestionAmountPerTest() : ?int
    {
        return $this->questionAmountPerTest;
    }
    
    /**
     * @param integer $lastQuestionSyncTimestamp
     */
    public function setLastQuestionSyncTimestamp($lastQuestionSyncTimestamp)
    {
        $this->lastQuestionSyncTimestamp = $lastQuestionSyncTimestamp;
    }
    
    /**
     * @return integer
     */
    public function getLastQuestionSyncTimestamp() : ?int
    {
        return $this->lastQuestionSyncTimestamp;
    }
    
    //fau: fixRandomTestBuildable - function to get messages
    public function getBuildableMessages() : array
    {
        return $this->buildableMessages;
    }
    // fau.
    
    // -----------------------------------------------------------------------------------------------------------------
    
    /**
     * initialises the current object instance with values
     * from matching properties within the passed array
     *
     * @param array $dataArray
     */
    public function initFromArray($dataArray)
    {
        foreach ($dataArray as $field => $value) {
            switch ($field) {
                case 'req_pools_homo_scored':		$this->setPoolsWithHomogeneousScoredQuestionsRequired($value);	break;
                case 'quest_amount_cfg_mode':		$this->setQuestionAmountConfigurationMode($value);				break;
                case 'quest_amount_per_test':		$this->setQuestionAmountPerTest($value);						break;
                case 'quest_sync_timestamp':		$this->setLastQuestionSyncTimestamp($value);					break;
            }
        }
    }
    
    /**
     * loads the question set config for current test from the database
     *
     * @return boolean
     */
    public function loadFromDb() : bool
    {
        $res = $this->db->queryF(
            "SELECT * FROM tst_rnd_quest_set_cfg WHERE test_fi = %s",
            array('integer'),
            array($this->testOBJ->getTestId())
        );
        
        while ($row = $this->db->fetchAssoc($res)) {
            $this->initFromArray($row);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * saves the question set config for current test to the database
     */
    public function saveToDb()
    {
        if ($this->dbRecordExists($this->testOBJ->getTestId())) {
            $this->updateDbRecord($this->testOBJ->getTestId());
        } else {
            $this->insertDbRecord($this->testOBJ->getTestId());
        }
    }

    /**
     * saves the question set config for test with given id to the database
     *
     * @param $testId
     */
    public function cloneToDbForTestId($testId)
    {
        $this->insertDbRecord($testId);
    }

    /**
     * deletes the question set config for current test from the database
     */
    public function deleteFromDb()
    {
        $this->db->manipulateF(
            "DELETE FROM tst_rnd_quest_set_cfg WHERE test_fi = %s",
            array('integer'),
            array($this->testOBJ->getTestId())
        );
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * checks wether a question set config for current test exists in the database
     *
     * @param $testId
     * @return boolean
     */
    private function dbRecordExists($testId) : bool
    {
        $res = $this->db->queryF(
            "SELECT COUNT(*) cnt FROM tst_rnd_quest_set_cfg WHERE test_fi = %s",
            array('integer'),
            array($testId)
        );
        
        $row = $this->db->fetchAssoc($res);
        
        return (bool) $row['cnt'];
    }

    /**
     * updates the record in the database that corresponds
     * to the question set config for the current test
     *
     * @param $testId
     */
    private function updateDbRecord($testId)
    {
        $this->db->update(
            'tst_rnd_quest_set_cfg',
            array(
                'req_pools_homo_scored' => array('integer', (int) $this->arePoolsWithHomogeneousScoredQuestionsRequired()),
                'quest_amount_cfg_mode' => array('text', $this->getQuestionAmountConfigurationMode()),
                'quest_amount_per_test' => array('integer', (int) $this->getQuestionAmountPerTest()),
                'quest_sync_timestamp' => array('integer', (int) $this->getLastQuestionSyncTimestamp())
            ),
            array(
                'test_fi' => array('integer', $testId)
            )
        );
    }
    
    /**
     * inserts a new record for the question set config
     * for the current test into the database
     *
     * @param $testId
     */
    private function insertDbRecord($testId)
    {
        $this->db->insert('tst_rnd_quest_set_cfg', array(
            'test_fi' => array('integer', $testId),
            'req_pools_homo_scored' => array('integer', (int) $this->arePoolsWithHomogeneousScoredQuestionsRequired()),
            'quest_amount_cfg_mode' => array('text', $this->getQuestionAmountConfigurationMode()),
            'quest_amount_per_test' => array('integer', (int) $this->getQuestionAmountPerTest()),
            'quest_sync_timestamp' => array('integer', (int) $this->getLastQuestionSyncTimestamp())
        ));
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function isQuestionSetConfigured() : bool
    {
        return (
            $this->getLastQuestionSyncTimestamp() != 0
            && $this->isQuestionAmountConfigComplete()
            && $this->hasSourcePoolDefinitions()
            && $this->isQuestionSetBuildable()
        );
    }

    public function isQuestionAmountConfigComplete() : bool
    {
        if ($this->isQuestionAmountConfigurationModePerPool()) {
            $sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->testOBJ);

            $sourcePoolDefinitionList->loadDefinitions();

            foreach ($sourcePoolDefinitionList as $definition) {
                /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */

                if ($definition->getQuestionAmount() < 1) {
                    return false;
                }
            }
        } elseif ($this->getQuestionAmountPerTest() < 1) {
            return false;
        }

        return true;
    }

    public function hasSourcePoolDefinitions() : bool
    {
        $sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->testOBJ);

        return $sourcePoolDefinitionList->savedDefinitionsExist();
    }

    public function isQuestionSetBuildable() : bool
    {
        $sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->testOBJ);
        $sourcePoolDefinitionList->loadDefinitions();

        $stagingPoolQuestionList = new ilTestRandomQuestionSetStagingPoolQuestionList($this->db, $this->component_repository);

        $questionSetBuilder = ilTestRandomQuestionSetBuilder::getInstance($this->db, $this->testOBJ, $this, $sourcePoolDefinitionList, $stagingPoolQuestionList);
        
        //fau: fixRandomTestBuildable - get messages if set is not buildable
        $buildable = $questionSetBuilder->checkBuildable();
        $this->buildableMessages = $questionSetBuilder->getCheckMessages();
        return $buildable;
        // fau.
        
        return $questionSetBuilder->checkBuildable();
    }
    
    public function doesQuestionSetRelatedDataExist() : bool
    {
        if ($this->dbRecordExists($this->testOBJ->getTestId())) {
            return true;
        }

        $sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->testOBJ);

        if ($sourcePoolDefinitionList->savedDefinitionsExist()) {
            return true;
        }

        return false;
    }
    
    public function removeQuestionSetRelatedData() : void
    {
        $sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->testOBJ);
        $sourcePoolDefinitionList->deleteDefinitions();

        require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolBuilder.php';
        $stagingPool = new ilTestRandomQuestionSetStagingPoolBuilder(
            $this->db,
            $this->testOBJ
        );
        $stagingPool->reset();

        $this->resetQuestionSetRelatedTestSettings();

        $this->deleteFromDb();
    }

    public function resetQuestionSetRelatedTestSettings()
    {
        $this->testOBJ->setResultFilterTaxIds(array());
        $this->testOBJ->saveToDb(true);
    }

    /**
     * removes all question set config related data for cloned/copied test
     *
     * @param ilObjTest $cloneTestOBJ
     */
    public function cloneQuestionSetRelatedData(ilObjTest $cloneTestOBJ)
    {
        // clone general config
        
        $this->loadFromDb();
        $this->cloneToDbForTestId($cloneTestOBJ->getTestId());

        // clone source pool definitions (selection rules)

        $sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($this->testOBJ);
        $sourcePoolDefinitionList->loadDefinitions();
        $definitionIdMap = $sourcePoolDefinitionList->cloneDefinitionsForTestId($cloneTestOBJ->getTestId());
        $this->registerClonedSourcePoolDefinitionIdMapping($cloneTestOBJ, $definitionIdMap);
        
        // build new question stage for cloned test

        $sourcePoolDefinitionList = $this->buildSourcePoolDefinitionList($cloneTestOBJ);
        $stagingPool = $this->buildStagingPoolBuilder($cloneTestOBJ);

        $sourcePoolDefinitionList->loadDefinitions();
        $stagingPool->rebuild($sourcePoolDefinitionList);
        $sourcePoolDefinitionList->saveDefinitions();
        
        $this->updateLastQuestionSyncTimestampForTestId($cloneTestOBJ->getTestId(), time());
    }
    
    private function registerClonedSourcePoolDefinitionIdMapping(ilObjTest $cloneTestOBJ, $definitionIdMap)
    {
        global $DIC;
        $ilLog = $DIC['ilLog'];
        
        require_once 'Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
        $cwo = ilCopyWizardOptions::_getInstance($cloneTestOBJ->getTmpCopyWizardCopyId());

        foreach ($definitionIdMap as $originalDefinitionId => $cloneDefinitionId) {
            $originalKey = $this->testOBJ->getRefId() . '_rndSelDef_' . $originalDefinitionId;
            $mappedKey = $cloneTestOBJ->getRefId() . '_rndSelDef_' . $cloneDefinitionId;
            $cwo->appendMapping($originalKey, $mappedKey);
            $ilLog->write(__METHOD__ . ": Added random selection definition id mapping $originalKey <-> $mappedKey");
        }
    }

    private function buildSourcePoolDefinitionList(ilObjTest $testOBJ) : ilTestRandomQuestionSetSourcePoolDefinitionList
    {
        require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
        $sourcePoolDefinitionFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
            $this->db,
            $testOBJ
        );

        require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
        $sourcePoolDefinitionList = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $this->db,
            $testOBJ,
            $sourcePoolDefinitionFactory
        );

        return $sourcePoolDefinitionList;
    }
    
    private function buildStagingPoolBuilder(ilObjTest $testOBJ) : ilTestRandomQuestionSetStagingPoolBuilder
    {
        require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolBuilder.php';
        $stagingPool = new ilTestRandomQuestionSetStagingPoolBuilder($this->db, $testOBJ);
        
        return $stagingPool;
    }
    
    // -----------------------------------------------------------------------------------------------------------------
    
    public function updateLastQuestionSyncTimestampForTestId($testId, $timestamp)
    {
        $this->db->update(
            'tst_rnd_quest_set_cfg',
            array(
                'quest_sync_timestamp' => array('integer', (int) $timestamp)
            ),
            array(
                'test_fi' => array('integer', $testId)
            )
        );
    }

    public function isResultTaxonomyFilterSupported() : bool
    {
        return true;
    }

    // -----------------------------------------------------------------------------------------------------------------
    
    public function getSelectableQuestionPools() : array
    {
        return $this->testOBJ->getAvailableQuestionpools(
            true,
            $this->arePoolsWithHomogeneousScoredQuestionsRequired(),
            false,
            true,
            true
        );
    }
    
    public function doesSelectableQuestionPoolsExist() : bool
    {
        return (bool) count($this->getSelectableQuestionPools());
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function areDepenciesBroken() : bool
    {
        return $this->testOBJ->isTestFinalBroken();
    }

    public function getDepenciesBrokenMessage(ilLanguage $lng) : string
    {
        return $lng->txt('tst_old_style_rnd_quest_set_broken');
    }

    public function isValidRequestOnBrokenQuestionSetDepencies($nextClass, $cmd) : bool
    {
        //vd($nextClass, $cmd);

        switch ($nextClass) {
            case 'ilobjectmetadatagui':
            case 'ilpermissiongui':

                return true;

            case 'ilobjtestgui':
            case '':

                $cmds = array(
                    'infoScreen', 'participants', 'npSetFilter', 'npResetFilter',
                    //'deleteAllUserResults', 'confirmDeleteAllUserResults',
                    //'deleteSingleUserResults', 'confirmDeleteSelectedUserData', 'cancelDeleteSelectedUserData'
                );

                if (in_array($cmd, $cmds)) {
                    return true;
                }

                break;
        }

        return false;
    }

    public function getHiddenTabsOnBrokenDepencies() : array
    {
        return array(
            'assQuestions', 'settings', 'manscoring', 'scoringadjust', 'statistics', 'history', 'export'
        );
    }

    // -----------------------------------------------------------------------------------------------------------------
    
    public function getCommaSeparatedSourceQuestionPoolLinks() : string
    {
        $definitionList = $this->buildSourcePoolDefinitionList($this->testOBJ);
        $definitionList->loadDefinitions();
        
        $poolTitles = array();
        
        foreach ($definitionList as $definition) {
            /* @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
            
            $refId = current(ilObject::_getAllReferences($definition->getPoolId()));
            $href = ilLink::_getLink($refId, 'qpl');
            $title = $definition->getPoolTitle();
            
            $poolTitles[$definition->getPoolId()] = "<a href=\"$href\" alt=\"$title\">$title</a>";
        }
        
        return implode(', ', $poolTitles);
    }
}
