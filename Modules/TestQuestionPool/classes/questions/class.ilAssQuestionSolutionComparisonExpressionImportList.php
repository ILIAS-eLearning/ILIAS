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
 * @implements Iterator<int, ilAssQuestionSolutionComparisonExpressionImport>
 */
class ilAssQuestionSolutionComparisonExpressionImportList implements Iterator
{
    private ?int $importQuestionId;
    private ?int $importSkillBaseId;
    private ?int $importSkillTrefId;
    /** @var array<int, ilAssQuestionSolutionComparisonExpressionImport>  */
    private array $expressions;

    public function __construct()
    {
        $this->importQuestionId = null;
        $this->importSkillBaseId = null;
        $this->importSkillTrefId = null;

        $this->expressions = [];
    }

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
     * @return array<int, ilAssQuestionSolutionComparisonExpressionImport>
     */
    public function getExpressions(): array
    {
        return $this->expressions;
    }

    public function addExpression(ilAssQuestionSolutionComparisonExpressionImport $expression): void
    {
        $expression->setImportQuestionId($this->getImportQuestionId());
        $expression->setImportSkillBaseId($this->getImportSkillBaseId());
        $expression->setImportSkillTrefId($this->getImportSkillTrefId());

        $this->expressions[$expression->getOrderIndex()] = $expression;
    }

    public function current(): ilAssQuestionSolutionComparisonExpressionImport
    {
        return current($this->expressions);
    }

    public function next(): void
    {
        next($this->expressions);
    }

    public function key(): int
    {
        return key($this->expressions);
    }

    public function valid(): bool
    {
        return key($this->expressions) !== null;
    }

    public function rewind(): void
    {
        reset($this->expressions);
    }
}
