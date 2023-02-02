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
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentImport
{
    /**
     * @var integer
     */
    private $importQuestionId;

    /**
     * @var integer
     */
    private $importSkillBaseId;

    /**
     * @var integer
     */
    private $importSkillTrefId;

    /**
     * @var string
     */
    private $importSkillTitle;

    /**
     * @var string
     */
    private $importSkillPath;

    /**
     * @var integer
     */
    private $skillPoints;

    /**
     * @var string
     */
    private $evalMode;

    /**
     * @var ilAssQuestionSolutionComparisonExpressionImportList
     */
    private $importSolutionComparisonExpressionList;

    /**
     * ilAssQuestionSkillAssignmentImport constructor.
     */
    public function __construct()
    {
        $this->importSolutionComparisonExpressionList = new ilAssQuestionSolutionComparisonExpressionImportList();
    }

    /**
     * @param int $questionId
     */
    public function setImportQuestionId($importQuestionId): void
    {
        $this->importQuestionId = $importQuestionId;
    }

    /**
     * @return int
     */
    public function getImportQuestionId(): int
    {
        return $this->importQuestionId;
    }

    /**
     * @param int $skillBaseId
     */
    public function setImportSkillBaseId($importSkillBaseId): void
    {
        $this->importSkillBaseId = $importSkillBaseId;
    }

    /**
     * @return int
     */
    public function getImportSkillBaseId(): int
    {
        return $this->importSkillBaseId;
    }

    /**
     * @param int $skillTrefId
     */
    public function setImportSkillTrefId($importSkillTrefId): void
    {
        $this->importSkillTrefId = $importSkillTrefId;
    }

    /**
     * @return int
     */
    public function getImportSkillTrefId(): int
    {
        return $this->importSkillTrefId;
    }

    /**
     * @return string
     */
    public function getImportSkillTitle(): string
    {
        return $this->importSkillTitle;
    }

    /**
     * @param string $importSkillTitle
     */
    public function setImportSkillTitle($importSkillTitle): void
    {
        $this->importSkillTitle = $importSkillTitle;
    }

    /**
     * @return string
     */
    public function getImportSkillPath(): string
    {
        return $this->importSkillPath;
    }

    /**
     * @param string $importSkillPath
     */
    public function setImportSkillPath($importSkillPath): void
    {
        $this->importSkillPath = $importSkillPath;
    }

    /**
     * @return string
     */
    public function getEvalMode(): string
    {
        return $this->evalMode;
    }

    /**
     * @param $evalMode
     */
    public function setEvalMode($evalMode): void
    {
        $this->evalMode = $evalMode;
    }

    /**
     * @return bool
     */
    public function hasImportEvalModeBySolution(): bool
    {
        return $this->getEvalMode() == ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_SOLUTION;
    }

    /**
     * @param int $skillPoints
     */
    public function setSkillPoints($skillPoints): void
    {
        $this->skillPoints = $skillPoints;
    }

    /**
     * @return int
     */
    public function getSkillPoints(): int
    {
        return $this->skillPoints ?? 0;
    }

    public function initImportSolutionComparisonExpressionList(): void
    {
        $this->importSolutionComparisonExpressionList->setImportQuestionId($this->getImportQuestionId());
        $this->importSolutionComparisonExpressionList->setImportSkillBaseId($this->getImportSkillBaseId());
        $this->importSolutionComparisonExpressionList->setImportSkillTrefId($this->getImportSkillTrefId());
    }

    /**
     * @return ilAssQuestionSolutionComparisonExpressionImportList
     */
    public function getImportSolutionComparisonExpressionList(): ilAssQuestionSolutionComparisonExpressionImportList
    {
        return $this->importSolutionComparisonExpressionList;
    }

    public function sleep(): void
    {
        // TODO: Implement __sleep() method.
    }

    public function wakeup(): void
    {
        // TODO: Implement __wakeup() method.
    }
}
