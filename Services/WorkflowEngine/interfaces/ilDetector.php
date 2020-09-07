<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilDetector Interface is part of the petri net based workflow engine.
 *
 * Please see the reference implementations for details:
 * @see class.ilSimpleDetector.php
 * @see class.ilCounterDetector.php
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
interface ilDetector
{
    /**
     * @param $params
     *
     * @return mixed
     */
    public function trigger($params);

    /**
     * @return mixed
     */
    public function getDetectorState();

    /**
     * @return mixed
     */
    public function onActivate();

    /**
     * @return mixed
     */
    public function onDeactivate();
}
