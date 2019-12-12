<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilEmitter Interface is part of the petri net based workflow engine.
 *
 * Please see the reference implementations for details:
 * @see class.ilSimpleEmitter.php
 * @see class.ilActivationEmitter.php
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
interface ilEmitter
{
    /**
     * @return mixed
     */
    public function emit();
}
