<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilActivity.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilWorkflowEngineElement.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilNode.php';

/**
 * Workflow Node of the petri net based workflow engine.
 *
 * This activity stops a running workflow. This activity is used to abort a workflow,
 * even if there are open nodes. This reduces complexity, since the threads do not need
 * to cross communicate and conditionally merge in order to be able to finish the
 * instances. Use with caution.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
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

    /** @var string $name */
    protected $name;

    /**
     * Default constructor.
     *
     * @param ilNode $context
     */
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
     *
     */
    public function execute()
    {
        /**
         * @var $workflow ilBaseWorkflow
         */
        $workflow = $this->context->getContext();
        $workflow->stopWorkflow();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        unset($this->context);
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
