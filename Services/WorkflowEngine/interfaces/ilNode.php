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

declare(strict_types=1);

/**
 * ilNode of the petri net based workflow engine.
 *
 * Please see the reference implementations for details:
 * @see class.ilBasicNode.php
 * @see class.ilConditionalNode.php
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
interface ilNode extends ilWorkflowEngineElement
{
    /**
     * @return mixed
     */
    public function attemptTransition();

    /**
     * @return mixed
     */
    public function checkTransitionPreconditions();

    /**
     * @return mixed
     */
    public function executeTransition();


    /**
     * @param ilDetector $detector
     * @return mixed
     */
    public function addDetector(ilDetector $detector);

    /**
     * @param ilEmitter $emitter
     * @param bool      $else
     * @return mixed
     */
    public function addEmitter(ilEmitter $emitter, bool $else = false);

    /**
     * @param ilActivity $activity
     * @param bool $else
     * @return mixed
     */
    public function addActivity(ilActivity $activity, bool $else = false);


    /**
     * @return mixed
     */
    public function activate();

    /**
     * @return mixed
     */
    public function deactivate();

    /**
     * @return mixed
     */
    public function onActivate();

    /**
     * @return mixed
     */
    public function onDeactivate();


    /**
     * @param ilDetector $detector
     * @return mixed
     */
    public function notifyDetectorSatisfaction(ilDetector $detector);

    /**
     * @return mixed
     */
    public function getDetectors();

    /**
     * @return mixed
     */
    public function getEmitters(bool $else = false);


    /**
     * @return mixed
     */
    public function getRuntimeVars();

    /**
     * @param array $runtime_vars
     * @return mixed
     */
    public function setRuntimeVars(array $runtime_vars);

    /**
     * @param string $name
     * @return mixed
     */
    public function getRuntimeVar(string $name);

    /**
     * @param string $name
     * @param mixed  $value
     * @return mixed
     */
    public function setRuntimeVar(string $name, $value);
}
