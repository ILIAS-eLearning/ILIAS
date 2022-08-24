<?php

declare(strict_types=1);

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
 * ilDetector Interface is part of the petri net based workflow engine.
 *
 * Please see the reference implementations for details:
 * @see class.ilSimpleDetector.php
 * @see class.ilCounterDetector.php
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
interface ilDetector
{
    public function trigger(?array $params): ?bool;

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
