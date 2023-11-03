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
class ilAssQuestionSolutionComparisonExpressionImport
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
     * @var integer
     */
    private $orderIndex;

    /**
     * @var string
     */
    private $expression;

    /**
     * @var integer
     */
    private $points;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->importQuestionId = null;
        $this->importSkillBaseId = null;
        $this->importSkillTrefId = null;
        $this->orderIndex = null;
        $this->expression = null;
        $this->points = null;
    }

    /**
     * @return int
     */
    public function getImportQuestionId(): ?int
    {
        return $this->importQuestionId;
    }

    /**
     * @param int $importQuestionId
     */
    public function setImportQuestionId($importQuestionId): void
    {
        $this->importQuestionId = $importQuestionId;
    }

    /**
     * @return int
     */
    public function getImportSkillBaseId(): ?int
    {
        return $this->importSkillBaseId;
    }

    /**
     * @param int $importSkillBaseId
     */
    public function setImportSkillBaseId($importSkillBaseId): void
    {
        $this->importSkillBaseId = $importSkillBaseId;
    }

    /**
     * @return int
     */
    public function getImportSkillTrefId(): ?int
    {
        return $this->importSkillTrefId;
    }

    /**
     * @param int $importSkillTrefId
     */
    public function setImportSkillTrefId($importSkillTrefId): void
    {
        $this->importSkillTrefId = $importSkillTrefId;
    }

    /**
     * @return int
     */
    public function getOrderIndex(): ?int
    {
        return $this->orderIndex;
    }

    /**
     * @param int $orderIndex
     */
    public function setOrderIndex($orderIndex): void
    {
        $this->orderIndex = $orderIndex;
    }

    /**
     * @return string
     */
    public function getExpression(): ?string
    {
        return $this->expression;
    }

    /**
     * @param string $expression
     */
    public function setExpression($expression): void
    {
        $this->expression = $expression;
    }

    /**
     * @return int
     */
    public function getPoints(): ?int
    {
        return $this->points;
    }

    /**
     * @param int $points
     */
    public function setPoints($points): void
    {
        $this->points = $points;
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
