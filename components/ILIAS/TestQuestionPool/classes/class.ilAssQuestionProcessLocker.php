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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
abstract class ilAssQuestionProcessLocker
{
    protected function executeOperation(callable $operation): void
    {
        $operation();
    }

    final public function executePersistWorkingStateLockOperation(callable $operation): void
    {
        $this->onBeforeExecutingPersistWorkingStateOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingPersistWorkingStateOperation();
    }

    protected function onBeforeExecutingPersistWorkingStateOperation(): void
    {
    }

    protected function onAfterExecutingPersistWorkingStateOperation(): void
    {
    }

    final public function executeUserSolutionUpdateLockOperation(callable $operation): void
    {
        $this->onBeforeExecutingUserSolutionUpdateOperation();
        $this->executeOperation($operation);
    }

    protected function onBeforeExecutingUserSolutionUpdateOperation(): void
    {
    }

    final public function executeUserQuestionResultUpdateOperation(callable $operation): void
    {
        $this->onBeforeExecutingUserQuestionResultUpdateOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingUserQuestionResultUpdateOperation();
    }

    protected function onBeforeExecutingUserQuestionResultUpdateOperation(): void
    {
    }

    protected function onAfterExecutingUserQuestionResultUpdateOperation(): void
    {
    }

    final public function executeUserPassResultUpdateLockOperation(callable $operation): void
    {
        $this->onBeforeExecutingUserPassResultUpdateOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingUserPassResultUpdateOperation();
    }

    protected function onBeforeExecutingUserPassResultUpdateOperation(): void
    {
    }

    protected function onAfterExecutingUserPassResultUpdateOperation(): void
    {
    }

    final public function executeUserTestResultUpdateLockOperation(callable $operation): void
    {
        $this->onBeforeExecutingUserTestResultUpdateOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingUserTestResultUpdateOperation();
    }

    protected function onBeforeExecutingUserTestResultUpdateOperation(): void
    {
    }

    protected function onAfterExecutingUserTestResultUpdateOperation(): void
    {
    }

    final public function executeUserSolutionAdoptLockOperation(callable $operation): void
    {
        $this->onBeforeExecutingUserSolutionAdoptOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingUserSolutionAdoptOperation();
    }

    protected function onBeforeExecutingUserSolutionAdoptOperation(): void
    {
    }

    protected function onAfterExecutingUserSolutionAdoptOperation(): void
    {
    }
}
