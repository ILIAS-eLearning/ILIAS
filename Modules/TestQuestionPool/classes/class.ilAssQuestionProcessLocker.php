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
    protected function executeOperation(callable $operation)
    {
        $operation();
    }

    /**
     * @param callable $operation
     */
    final public function executePersistWorkingStateLockOperation(callable $operation)
    {
        $this->onBeforeExecutingPersistWorkingStateOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingPersistWorkingStateOperation();
    }

    /**
     *
     */
    protected function onBeforeExecutingPersistWorkingStateOperation()
    {
    }

    /**
     *
     */
    protected function onAfterExecutingPersistWorkingStateOperation()
    {
    }

    /**
     * @param callable $operation
     */
    final public function executeUserSolutionUpdateLockOperation(callable $operation)
    {
        $this->onBeforeExecutingUserSolutionUpdateOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingUserSolutionUpdateOperation();
    }

    /**
     *
     */
    protected function onBeforeExecutingUserSolutionUpdateOperation()
    {
    }

    /**
     *
     */
    protected function onAfterExecutingUserSolutionUpdateOperation()
    {
    }

    /**
     * @param callable $operation
     */
    final public function executeUserQuestionResultUpdateOperation(callable $operation)
    {
        $this->onBeforeExecutingUserQuestionResultUpdateOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingUserQuestionResultUpdateOperation();
    }

    /**
     *
     */
    protected function onBeforeExecutingUserQuestionResultUpdateOperation()
    {
    }

    /**
     *
     */
    protected function onAfterExecutingUserQuestionResultUpdateOperation()
    {
    }

    /**
     * @param callable $operation
     */
    final public function executeUserPassResultUpdateLockOperation(callable $operation)
    {
        $this->onBeforeExecutingUserPassResultUpdateOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingUserPassResultUpdateOperation();
    }

    /**
     *
     */
    protected function onBeforeExecutingUserPassResultUpdateOperation()
    {
    }

    /**
     *
     */
    protected function onAfterExecutingUserPassResultUpdateOperation()
    {
    }

    /**
     * @param callable $operation
     */
    final public function executeUserTestResultUpdateLockOperation(callable $operation)
    {
        $this->onBeforeExecutingUserTestResultUpdateOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingUserTestResultUpdateOperation();
    }

    /**
     *
     */
    protected function onBeforeExecutingUserTestResultUpdateOperation()
    {
    }

    /**
     *
     */
    protected function onAfterExecutingUserTestResultUpdateOperation()
    {
    }

    /**
     * @param callable $operation
     */
    final public function executeUserSolutionAdoptLockOperation(callable $operation)
    {
        $this->onBeforeExecutingUserSolutionAdoptOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingUserSolutionAdoptOperation();
    }

    /**
     *
     */
    protected function onBeforeExecutingUserSolutionAdoptOperation()
    {
    }

    /**
     *
     */
    protected function onAfterExecutingUserSolutionAdoptOperation()
    {
    }
}
