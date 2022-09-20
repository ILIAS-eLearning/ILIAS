<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
abstract class ilAssQuestionProcessLocker
{
    /**
     * @param callable $operation
     */
    protected function executeOperation(callable $operation): void
    {
        $operation();
    }

    /**
     * @param callable $operation
     */
    final public function executePersistWorkingStateLockOperation(callable $operation): void
    {
        $this->onBeforeExecutingPersistWorkingStateOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingPersistWorkingStateOperation();
    }

    /**
     *
     */
    protected function onBeforeExecutingPersistWorkingStateOperation(): void
    {
    }

    /**
     *
     */
    protected function onAfterExecutingPersistWorkingStateOperation(): void
    {
    }

    /**
     * @param callable $operation
     */
    final public function executeUserSolutionUpdateLockOperation(callable $operation): void
    {
        $this->onBeforeExecutingUserSolutionUpdateOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingUserSolutionUpdateOperation();
    }

    /**
     *
     */
    protected function onBeforeExecutingUserSolutionUpdateOperation(): void
    {
    }

    /**
     *
     */
    protected function onAfterExecutingUserSolutionUpdateOperation(): void
    {
    }

    /**
     * @param callable $operation
     */
    final public function executeUserQuestionResultUpdateOperation(callable $operation): void
    {
        $this->onBeforeExecutingUserQuestionResultUpdateOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingUserQuestionResultUpdateOperation();
    }

    /**
     *
     */
    protected function onBeforeExecutingUserQuestionResultUpdateOperation(): void
    {
    }

    /**
     *
     */
    protected function onAfterExecutingUserQuestionResultUpdateOperation(): void
    {
    }

    /**
     * @param callable $operation
     */
    final public function executeUserPassResultUpdateLockOperation(callable $operation): void
    {
        $this->onBeforeExecutingUserPassResultUpdateOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingUserPassResultUpdateOperation();
    }

    /**
     *
     */
    protected function onBeforeExecutingUserPassResultUpdateOperation(): void
    {
    }

    /**
     *
     */
    protected function onAfterExecutingUserPassResultUpdateOperation(): void
    {
    }

    /**
     * @param callable $operation
     */
    final public function executeUserTestResultUpdateLockOperation(callable $operation): void
    {
        $this->onBeforeExecutingUserTestResultUpdateOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingUserTestResultUpdateOperation();
    }

    /**
     *
     */
    protected function onBeforeExecutingUserTestResultUpdateOperation(): void
    {
    }

    /**
     *
     */
    protected function onAfterExecutingUserTestResultUpdateOperation(): void
    {
    }

    /**
     * @param callable $operation
     */
    final public function executeUserSolutionAdoptLockOperation(callable $operation): void
    {
        $this->onBeforeExecutingUserSolutionAdoptOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingUserSolutionAdoptOperation();
    }

    /**
     *
     */
    protected function onBeforeExecutingUserSolutionAdoptOperation(): void
    {
    }

    /**
     *
     */
    protected function onAfterExecutingUserSolutionAdoptOperation(): void
    {
    }
}
