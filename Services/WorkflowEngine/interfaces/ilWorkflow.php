<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilDetector.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilNode.php';

/**
 * ilWorkflow Interface is part of the petri net based workflow engine.
 *
 * Please see the reference / tutorial implementations for details:
 * @see class.ilBaseWorkflow.php (Base class)
 * @see class.ilPetriNetWorkflow1.php (Abstract Tutorial Part I)
 * @see class.ilPetriNetWorkflow2.php (Abstract Tutorial Part II)
 * @see class.ilBasicComplianceWorkflow.php (Real World Example)
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
interface ilWorkflow
{
    // Event handling

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function handleEvent($params);

    // Node management

    /**
     * @param \ilNode $node
     *
     * @return mixed
     */
    public function addNode(ilNode $node);

    /**
     * @param \ilNode $node
     *
     * @return mixed
     */
    public function setStartNode(ilNode $node);

    /**
     * @param \ilDetector $detector
     *
     * @return mixed
     */
    public function registerDetector(ilDetector $detector);

    // Status
    /**
     * @return mixed
     */
    public function startWorkflow();

    /**
     * @return mixed
     */
    public function stopWorkflow();

    /**
     * @return mixed
     */
    public function isActive();

    /**
     * @return mixed
     */
    public function onStartWorkflow();

    /**
     * @return mixed
     */
    public function onStopWorkflow();

    /**
     * @return mixed
     */
    public function onWorkflowFinished();

    // Persistence scheme.
    /**
     * @return mixed
     */
    public function getWorkflowData();

    /**
     * @return mixed
     */
    public function getWorkflowSubject();

    /**
     * @return mixed
     */
    public function getWorkflowContext();

    /**
     * @return mixed
     */
    public function getWorkflowClass();

    /**
     * @return mixed
     */
    public function getWorkflowLocation();

    /**
     * @param integer $id
     *
     * @return mixed
     */
    public function setDbId($id);

    /**
     * @return integer
     */
    public function getDbId();

    /**
     * @return bool
     */
    public function hasDbId();

    /**
     * @return mixed
     */
    public function isDataPersistenceRequired();

    /**
     * @return mixed
     */
    public function resetDataPersistenceRequirement();

    // Instance vars (data objects)

    /**
     * @param string $id
     * @param string $name
     *
     * @return mixed
     */
    public function defineInstanceVar($id, $name);

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function hasInstanceVarByName($name);

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function hasInstanceVarById($id);

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getInstanceVarByName($name);

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function getInstanceVarById($id);

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setInstanceVarByName($name, $value);

    /**
     * @param string $id
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setInstanceVarById($id, $value);

    /**
     * @return mixed
     */
    public function getInstanceVars();

    /**
     * @return mixed
     */
    public function flushInstanceVars();
}
