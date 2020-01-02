<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilDetector.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilEmitter.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilActivity.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilWorkflowEngineElement.php';

/**
 * ilNode of the petri net based workflow engine.
 *
 * Please see the reference implementations for details:
 * @see class.ilBasicNode.php
 * @see class.ilConditionalNode.php
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
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
     * @param \ilDetector $detector
     *
     * @return mixed
     */
    public function addDetector(ilDetector $detector);

    /**
     * @param \ilEmitter $emitter
     *
     * @return mixed
     */
    public function addEmitter(ilEmitter $emitter);

    /**
     * @param \ilActivity $activity
     *
     * @return mixed
     */
    public function addActivity(ilActivity $activity);


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
     * @param \ilDetector $detector
     *
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
    public function getEmitters();


    /**
     * @return mixed
     */
    public function getRuntimeVars();

    /**
     * @param array $runtime_vars
     *
     * @return mixed
     */
    public function setRuntimeVars($runtime_vars);

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getRuntimeVar($name);

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setRuntimeVar($name, $value);
}
