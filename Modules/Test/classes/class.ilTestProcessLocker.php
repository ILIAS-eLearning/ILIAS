<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
abstract class ilTestProcessLocker
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
    final public function executeTestStartLockOperation(callable $operation)
    {
        $this->onBeforeExecutingTestStartOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingTestStartOperation();
    }

    /**
     *
     */
    protected function onBeforeExecutingTestStartOperation()
    {
    }

    /**
     *
     */
    protected function onAfterExecutingTestStartOperation()
    {
    }

    /**
     * @param callable $operation
     * @param bool     $withTaxonomyTables
     */
    final public function executeRandomPassBuildOperation(callable $operation, $withTaxonomyTables = false)
    {
        $this->onBeforeExecutingRandomPassBuildOperation($withTaxonomyTables);
        $this->executeOperation($operation);
        $this->onAfterExecutingRandomPassBuildOperation($withTaxonomyTables);
    }

    /**
     * @param bool $withTaxonomyTables
     */
    protected function onBeforeExecutingRandomPassBuildOperation($withTaxonomyTables = false)
    {
    }

    /**
     * @param bool $withTaxonomyTables
     */
    protected function onAfterExecutingRandomPassBuildOperation($withTaxonomyTables = false)
    {
    }
    
    
    /**
     * @param callable $operation
     */
    final public function executeTestFinishOperation(callable $operation)
    {
        $this->onBeforeExecutingTestFinishOperation();
        $this->executeOperation($operation);
        $this->onAfterExecutingTestFinishOperation();
    }

    /**
     *
     */
    protected function onBeforeExecutingTestFinishOperation()
    {
    }

    /**
     *
     */
    protected function onAfterExecutingTestFinishOperation()
    {
    }
}
