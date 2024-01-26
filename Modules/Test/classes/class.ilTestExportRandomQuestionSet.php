<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestExport.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestExportRandomQuestionSet extends ilTestExport
{
    /**
     * @var ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    protected $srcPoolDefList;

    /**
     * @var array[ilTestRandomQuestionSetStagingPoolQuestionList]
     */
    protected $stagingPoolQuestionListByPoolId;
    
    protected function initXmlExport()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
        $srcPoolDefFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
            $ilDB,
            $this->test_obj
        );
        
        require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
        $this->srcPoolDefList = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $ilDB,
            $this->test_obj,
            $srcPoolDefFactory
        );

        $this->srcPoolDefList->loadDefinitions();

        // fau: fixRandomTestExportPages - use the complete random question list
        //		ilObjTest::exportPagesXML() uses $this->questions
        //		ilObjTest::loadQuestions() loads only those of the current active_id of ilUser
        $this->test_obj->questions = $this->getQuestionIds();
        // fau.
        require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolQuestionList.php';

        $this->stagingPoolQuestionListByPoolId = array();
    }

    protected function populateQuestionSetConfigXml(ilXmlWriter $xmlWriter)
    {
        $xmlWriter->xmlStartTag('RandomQuestionSetConfig');
        $this->populateCommonSettings($xmlWriter);
        $this->populateQuestionStages($xmlWriter);
        $this->populateSelectionDefinitions($xmlWriter);
        $xmlWriter->xmlEndTag('RandomQuestionSetConfig');
    }
    
    protected function populateCommonSettings(ilXmlWriter $xmlWriter)
    {
        global $DIC;
        $tree = $DIC['tree'];
        $ilDB = $DIC['ilDB'];
        $ilPluginAdmin = $DIC['ilPluginAdmin'];
        
        require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetConfig.php';
        $questionSetConfig = new ilTestRandomQuestionSetConfig($tree, $ilDB, $ilPluginAdmin, $this->test_obj);
        $questionSetConfig->loadFromDb();

        $xmlWriter->xmlElement('RandomQuestionSetSettings', array(
            'amountMode' => $questionSetConfig->getQuestionAmountConfigurationMode(),
            'questAmount' => $questionSetConfig->getQuestionAmountPerTest(),
            'homogeneous' => $questionSetConfig->arePoolsWithHomogeneousScoredQuestionsRequired(),
            'synctimestamp' => $questionSetConfig->getLastQuestionSyncTimestamp()
        ));
    }
    
    protected function populateQuestionStages(ilXmlWriter $xmlWriter)
    {
        $xmlWriter->xmlStartTag('RandomQuestionStage');
            
        foreach ($this->srcPoolDefList->getInvolvedSourcePoolIds() as $poolId) {
            $questionList = $this->getLoadedStagingPoolQuestionList($poolId);
            
            $xmlWriter->xmlStartTag('RandomQuestionStagingPool', array('poolId' => $poolId));
            $xmlWriter->xmlData(implode(',', $questionList->getQuestions()));
            $xmlWriter->xmlEndTag('RandomQuestionStagingPool');
        }

        $xmlWriter->xmlEndTag('RandomQuestionStage');
    }

    protected function populateSelectionDefinitions(ilXmlWriter $xmlWriter)
    {
        $xmlWriter->xmlStartTag('RandomQuestionSelectionDefinitions');
        
        foreach ($this->srcPoolDefList as $definition) {
            $attributes = array(
                'id' => $definition->getId(),
                'ref_id' => $definition->getPoolRefId(),
                'poolId' => $definition->getPoolId(),
                'poolQuestCount' => $definition->getPoolQuestionCount(),
                'questAmount' => $definition->getQuestionAmount(),
                'position' => $definition->getSequencePosition(),
                'typeFilter' => implode(',', $definition->getTypeFilterAsTypeTags()),
            );

            // #21330
            $mappedTaxFilter = $definition->getMappedTaxonomyFilter();
            if (is_array($mappedTaxFilter) && count($mappedTaxFilter) > 0) {
                $attributes['taxFilter'] = serialize($mappedTaxFilter);
            }

            $xmlWriter->xmlStartTag('RandomQuestionSelectionDefinition', $attributes);
            $xmlWriter->xmlElement('RandomQuestionSourcePoolTitle', null, $definition->getPoolTitle());
            $xmlWriter->xmlElement('RandomQuestionSourcePoolPath', null, $definition->getPoolPath());
            $xmlWriter->xmlEndTag('RandomQuestionSelectionDefinition');
        }
        
        $xmlWriter->xmlEndTag('RandomQuestionSelectionDefinitions');
    }

    protected function getQuestionsQtiXml()
    {
        $questionQtiXml = '';

        foreach ($this->srcPoolDefList->getInvolvedSourcePoolIds() as $poolId) {
            $questionList = $this->getLoadedStagingPoolQuestionList($poolId);
            
            foreach ($questionList as $questionId) {
                $questionQtiXml .= $this->getQuestionQtiXml($questionId);
            }
        }

        return $questionQtiXml;
    }
    
    /**
     * @return array
     */
    protected function getQuestionIds()
    {
        $questionIds = array();
        
        foreach ($this->srcPoolDefList->getInvolvedSourcePoolIds() as $poolId) {
            $questionList = $this->getLoadedStagingPoolQuestionList($poolId);
            
            foreach ($questionList as $questionId) {
                $questionIds[] = $questionId;
            }
        }
        
        return $questionIds;
    }

    /**
     * @param $poolId
     * @return ilTestRandomQuestionSetStagingPoolQuestionList
     */
    protected function getLoadedStagingPoolQuestionList($poolId)
    {
        if (!isset($this->stagingPoolQuestionListByPoolId[$poolId])) {
            global $DIC;
            $ilDB = $DIC['ilDB'];
            $ilPluginAdmin = $DIC['ilPluginAdmin'];
            
            $questionList = new ilTestRandomQuestionSetStagingPoolQuestionList($ilDB, $ilPluginAdmin);
            $questionList->setTestId($this->test_obj->getTestId());
            $questionList->setPoolId($poolId);
            $questionList->loadQuestions();
            
            $this->stagingPoolQuestionListByPoolId[$poolId] = $questionList;
        }

        return $this->stagingPoolQuestionListByPoolId[$poolId];
    }
}
