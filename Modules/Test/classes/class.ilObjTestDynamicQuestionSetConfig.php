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
class ilObjTestDynamicQuestionSetConfig extends ilTestQuestionSetConfig
{
    /**
     * id of question pool to be used as source
     *
     * @var integer
     */
    private $sourceQuestionPoolId = null;

    /**
     * @var boolean
     */
    private $answerStatusFilterEnabled = null;

    /**
     * the fact wether a taxonomie filter
     * can be used by test takers or not
     *
     * @var boolean
     */
    private $taxonomyFilterEnabled = null;

    /**
     * the id of taxonomy used for ordering the questions
     *
     * @var integer
     */
    private $orderingTaxonomyId = null;

    /**
     * getter for source question pool id
     *
     * @return integer
     */
    public function getSourceQuestionPoolId(): ?int
    {
        return $this->sourceQuestionPoolId;
    }

    /**
     * getter for source question pool id
     *
     * @param integer $sourceQuestionPoolId
     */
    public function setSourceQuestionPoolId($sourceQuestionPoolId)
    {
        $this->sourceQuestionPoolId = (int) $sourceQuestionPoolId;
    }

    /**
     * getter for source question pool title
     *
     * @return string
     */
    public function getSourceQuestionPoolTitle(): string
    {
        return $this->sourceQuestionPoolTitle;
    }

    /**
     * getter for source question pool title
     *
     * @param string $sourceQuestionPoolTitle
     */
    public function setSourceQuestionPoolTitle($sourceQuestionPoolTitle)
    {
        $this->sourceQuestionPoolTitle = $sourceQuestionPoolTitle;
    }

    /**
     * @return boolean
     */
    public function isAnswerStatusFilterEnabled(): ?bool
    {
        return $this->answerStatusFilterEnabled;
    }

    /**
     * @param boolean $answerStatusFilterEnabled
     */
    public function setAnswerStatusFilterEnabled($answerStatusFilterEnabled)
    {
        $this->answerStatusFilterEnabled = $answerStatusFilterEnabled;
    }

    /**
     * isser for taxonomie filter enabled
     *
     * @return boolean
     */
    public function isTaxonomyFilterEnabled(): ?bool
    {
        return $this->taxonomyFilterEnabled;
    }

    /**
     * setter for taxonomie filter enabled
     *
     * @param boolean $taxonomyFilterEnabled
     */
    public function setTaxonomyFilterEnabled($taxonomyFilterEnabled)
    {
        $this->taxonomyFilterEnabled = (bool) $taxonomyFilterEnabled;
    }

    /**
     * setter for ordering taxonomy id
     *
     * @return integer $orderingTaxonomyId
     */
    public function getOrderingTaxonomyId(): ?int
    {
        return $this->orderingTaxonomyId;
    }

    /**
     * getter for ordering taxonomy id
     *
     * @param integer $orderingTaxonomyId
     */
    public function setOrderingTaxonomyId($orderingTaxonomyId)
    {
        $this->orderingTaxonomyId = $orderingTaxonomyId;
    }

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
                case 'source_qpl_fi':				$this->setSourceQuestionPoolId($value);			break;
                case 'source_qpl_title':			$this->setSourceQuestionPoolTitle($value);		break;
                case 'answer_filter_enabled':		$this->setAnswerStatusFilterEnabled($value);	break;
                case 'tax_filter_enabled':			$this->setTaxonomyFilterEnabled($value);		break;
                case 'order_tax':					$this->setOrderingTaxonomyId($value);			break;
            }
        }
    }

    /**
     * loads the question set config for current test from the database
     *
     * @return boolean
     */
    public function loadFromDb(): bool
    {
        $res = $this->db->queryF(
            "SELECT * FROM tst_dyn_quest_set_cfg WHERE test_fi = %s",
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
     *
     * @return boolean
     */
    public function deleteFromDb(): bool
    {
        $aff = $this->db->manipulateF(
            "DELETE FROM tst_dyn_quest_set_cfg WHERE test_fi = %s",
            array('integer'),
            array($this->testOBJ->getTestId())
        );

        return (bool) $aff;
    }

    /**
     * checks wether a question set config for current test exists in the database
     *
     * @param $testId
     * @return boolean
     */
    private function dbRecordExists($testId): bool
    {
        $res = $this->db->queryF(
            "SELECT COUNT(*) cnt FROM tst_dyn_quest_set_cfg WHERE test_fi = %s",
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
            'tst_dyn_quest_set_cfg',
            array(
                'source_qpl_fi' => array('integer', $this->getSourceQuestionPoolId()),
                'source_qpl_title' => array('text', $this->getSourceQuestionPoolTitle()),
                'answer_filter_enabled' => array('integer', $this->isAnswerStatusFilterEnabled()),
                'tax_filter_enabled' => array('integer', $this->isTaxonomyFilterEnabled()),
                'order_tax' => array('integer', $this->getOrderingTaxonomyId())
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
        $this->db->insert('tst_dyn_quest_set_cfg', array(
                'test_fi' => array('integer', $testId),
                'source_qpl_fi' => array('integer', $this->getSourceQuestionPoolId()),
                'source_qpl_title' => array('text', $this->getSourceQuestionPoolTitle()),
                'answer_filter_enabled' => array('integer', $this->isAnswerStatusFilterEnabled()),
                'tax_filter_enabled' => array('integer', $this->isTaxonomyFilterEnabled()),
                'order_tax' => array('integer', $this->getOrderingTaxonomyId())
        ));
    }

    /**
     * returns the fact wether a useable question set config exists or not
     *
     * @return boolean
     */
    public function isQuestionSetConfigured(): bool
    {
        return $this->getSourceQuestionPoolId() > 0;
    }

    /**
     * returns the fact wether a useable question set config exists or not
     *
     * @return boolean
     */
    public function doesQuestionSetRelatedDataExist(): bool
    {
        return $this->isQuestionSetConfigured();
    }

    /**
     * removes all question set config related data
     * (in this case it's only the config itself)
     */
    public function removeQuestionSetRelatedData(): void
    {
        $this->deleteFromDb();
    }

    public function resetQuestionSetRelatedTestSettings()
    {
        // nothing to do
    }

    /**
     * removes all question set config related data for cloned/copied test
     *
     * @param ilObjTest $cloneTestOBJ
     */
    public function cloneQuestionSetRelatedData(ilObjTest $cloneTestOBJ)
    {
        $this->loadFromDb();
        $this->cloneToDbForTestId($cloneTestOBJ->getTestId());
    }

    /**
     * @param ilLanguage $lng
     * @param ilTree $tree
     * @return string
     */
    public function getSourceQuestionPoolSummaryString(ilLanguage $lng): string
    {
        $poolRefs = $this->getSourceQuestionPoolRefIds();

        if (!count($poolRefs)) {
            $sourceQuestionPoolSummaryString = sprintf(
                $lng->txt('tst_dyn_quest_set_src_qpl_summary_string_deleted'),
                $this->getSourceQuestionPoolTitle()
            );

            return $sourceQuestionPoolSummaryString;
        }

        foreach ($poolRefs as $refId) {
            if (!$this->tree->isDeleted($refId)) {
                $sourceQuestionPoolSummaryString = sprintf(
                    $lng->txt('tst_dynamic_question_set_source_questionpool_summary_string'),
                    $this->getSourceQuestionPoolTitle(),
                    $this->getQuestionPoolPathString($this->getSourceQuestionPoolId()),
                    $this->getSourceQuestionPoolNumQuestions()
                );

                return $sourceQuestionPoolSummaryString;
            }
        }

        $sourceQuestionPoolSummaryString = sprintf(
            $lng->txt('tst_dyn_quest_set_src_qpl_summary_string_trashed'),
            $this->getSourceQuestionPoolTitle(),
            $this->getSourceQuestionPoolNumQuestions()
        );

        return $sourceQuestionPoolSummaryString;
    }

    /**
     * @return integer
     */
    private function getSourceQuestionPoolNumQuestions(): int
    {
        $query = "
			SELECT COUNT(*) num from qpl_questions
			WHERE obj_fi = %s AND original_id IS NULL
		";

        $res = $this->db->queryF(
            $query,
            array('integer'),
            array($this->getSourceQuestionPoolId())
        );

        $row = $this->db->fetchAssoc($res);

        return $row['num'];
    }

    public function areDepenciesInVulnerableState(): bool
    {
        if (!$this->getSourceQuestionPoolId()) {
            return false;
        }

        $poolRefs = $this->getSourceQuestionPoolRefIds();

        foreach ($poolRefs as $refId) {
            if (!$this->tree->isDeleted($refId)) {
                return false;
            }
        }

        return true;
    }

    public function getDepenciesInVulnerableStateMessage(ilLanguage $lng): string
    {
        $msg = sprintf(
            $lng->txt('tst_dyn_quest_set_pool_trashed'),
            $this->getSourceQuestionPoolTitle()
        );

        return $msg;
    }

    public function areDepenciesBroken(): bool
    {
        if (!$this->getSourceQuestionPoolId()) {
            return false;
        }

        $poolRefs = $this->getSourceQuestionPoolRefIds();

        if (count($poolRefs)) {
            return false;
        }

        return true;
    }

    public function getDepenciesBrokenMessage(ilLanguage $lng): string
    {
        $msg = sprintf(
            $lng->txt('tst_dyn_quest_set_pool_deleted'),
            $this->getSourceQuestionPoolTitle()
        );

        return $msg;
    }

    public function isValidRequestOnBrokenQuestionSetDepencies($nextClass, $cmd): bool
    {
        //vd($nextClass, $cmd);

        if (!$this->testOBJ->participantDataExist()) {
            return true;
        }

        switch ($nextClass) {
            case 'ilobjtestdynamicquestionsetconfiggui':

            case 'ilobjectmetadatagui':
            case 'ilpermissiongui':

                return true;

            case 'ilobjtestgui':
            case '':

                $cmds = array(
                    'infoScreen', 'participants', 'npSetFilter', 'npResetFilter',
                    'deleteAllUserResults', 'confirmDeleteAllUserResults',
                    'deleteSingleUserResults', 'confirmDeleteSelectedUserData', 'cancelDeleteSelectedUserData'
                );

                if (in_array($cmd, $cmds)) {
                    return true;
                }

                break;
        }

        return false;
    }

    public function getHiddenTabsOnBrokenDepencies(): array
    {
        return array(
            'settings', 'manscoring', 'scoringadjust', 'statistics', 'history', 'export'
        );
    }

    private $sourceQuestionPoolRefIds = null;

    public function getSourceQuestionPoolRefIds(): array
    {
        if ($this->sourceQuestionPoolRefIds === null) {
            $this->sourceQuestionPoolRefIds = ilObject::_getAllReferences($this->getSourceQuestionPoolId());
        }

        return $this->sourceQuestionPoolRefIds;
    }

    public function isResultTaxonomyFilterSupported(): bool
    {
        return false;
    }

    public function isAnyQuestionFilterEnabled(): bool
    {
        if ($this->isTaxonomyFilterEnabled()) {
            return true;
        }

        if ($this->isAnswerStatusFilterEnabled()) {
            return true;
        }

        return false;
    }

    public function getSourceQuestionPoolLink(): string
    {
        $refId = current(ilObject::_getAllReferences($this->getSourceQuestionPoolId()));
        $href = ilLink::_getLink($refId, 'qpl');
        $title = $this->getSourceQuestionPoolTitle();

        return "<a href=\"$href\" alt=\"$title\">$title</a>";
    }
}
