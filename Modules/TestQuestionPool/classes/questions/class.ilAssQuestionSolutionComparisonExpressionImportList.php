<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSolutionComparisonExpressionImport.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSolutionComparisonExpressionImportList implements Iterator
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
     * @var array
     */
    private $expressions;

    /**
     * ilAssQuestionSolutionComparisonExpressionImportList constructor.
     */
    public function __construct()
    {
        $this->importQuestionId = null;
        $this->importSkillBaseId = null;
        $this->importSkillTrefId = null;

        $this->expressions = array();
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
     * @return array
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

    /**
     * @return ilAssQuestionSolutionComparisonExpressionImport
     */
    public function current(): ilAssQuestionSolutionComparisonExpressionImport
    {
        return current($this->expressions);
    }

    /**
     * @return ilAssQuestionSolutionComparisonExpressionImport
     */
    public function next(): ilAssQuestionSolutionComparisonExpressionImport
    {
        return next($this->expressions);
    }

    /**
     * @return integer|bool
     */
    public function key()
    {
        return key($this->expressions);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->expressions) !== null;
    }

    /**
     * @return ilAssQuestionSolutionComparisonExpressionImport|bool
     */
    public function rewind()
    {
        return reset($this->expressions);
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
