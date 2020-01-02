<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilActivity Interface is part of the petri net based workflow engine.
 *
 * Please see the reference implemenations for details:
 * @see class.ilLoggingActivity.php
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
interface ilActivity
{
    /**
     * @return mixed
     */
    public function execute();
}
