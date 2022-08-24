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
 * ilWorkflow Interface is part of the petri net based workflow engine.
 *
 * Please see the reference / tutorial implementations for details:
 * @see class.ilBaseWorkflow.php (Base class)
 * @see class.ilPetriNetWorkflow1.php (Abstract Tutorial Part I)
 * @see class.ilPetriNetWorkflow2.php (Abstract Tutorial Part II)
 * @see class.ilBasicComplianceWorkflow.php (Real World Example)
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
interface ilWorkflow
{
    // Event handling

    /**
     * @param array $params
     * @return mixed
     */
    public function handleEvent(array $params);

    // Node management

    /**
     * @param ilNode $node
     * @return mixed
     */
    public function addNode(ilNode $node);

    /**
     * @param ilNode $node
     * @return mixed
     */
    public function setStartNode(ilNode $node);

    /**
     * @param ilDetector $detector
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
     * @return mixed
     */
    public function setDbId(int $id);

    /**
     * @return integer
     */
    public function getDbId(): int;

    /**
     * @return bool
     */
    public function hasDbId(): bool;

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
     * @return mixed
     */
    public function defineInstanceVar(string $id, string $name);

    /**
     * @param string $name
     * @return mixed
     */
    public function hasInstanceVarByName(string $name);

    /**
     * @param string $id
     * @return mixed
     */
    public function hasInstanceVarById(string $id);

    /**
     * @param string $name
     * @return mixed
     */
    public function getInstanceVarByName(string $name);

    /**
     * @param string $id
     * @return mixed
     */
    public function getInstanceVarById(string $id);

    /**
     * @param string $name
     * @param mixed  $value
     * @return mixed
     */
    public function setInstanceVarByName(string $name, $value);

    /**
     * @param string $id
     * @param mixed  $value
     * @return mixed
     */
    public function setInstanceVarById(string $id, $value);

    /**
     * @return mixed
     */
    public function getInstanceVars();

    /**
     * @return mixed
     */
    public function flushInstanceVars();
}
