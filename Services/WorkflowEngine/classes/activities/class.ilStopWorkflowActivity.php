<?php declare(strict_types=1);

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
 * Workflow Node of the petri net based workflow engine.
 *
 * This activity stops a running workflow. This activity is used to abort a workflow,
 * even if there are open nodes. This reduces complexity, since the threads do not need
 * to cross communicate and conditionally merge in order to be able to finish the
 * instances. Use with caution.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilStopWorkflowActivity implements ilActivity, ilWorkflowEngineElement
{
    /**
     * Holds a reference to the parent object
     *
     * @var ilWorkflowEngineElement
     */
    private $context;

    protected string $name;

    public function __construct(ilNode $context)
    {
        $this->context = $context;
    }

    /**
     * Returns the parent object. Type is ilNode, implements ilWorkflowEngineElement
     *
     * @return ilNode Parent node of this element.
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Executes this action according to its settings.
     *
     * @todo Use exceptions / internal logging.
     */
    public function execute() : void
    {
        /**
         * @var ilBaseWorkflow $workflow
         */
        $workflow = $this->context->getContext();
        $workflow->stopWorkflow();
    }

    public function __destruct()
    {
        unset($this->context);
    }

    public function setName($name) : void
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }
}
