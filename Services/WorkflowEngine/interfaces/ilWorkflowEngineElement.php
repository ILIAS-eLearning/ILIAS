<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilWorkflowEngineElement Interface is part of the petri net based workflow engine.
 *
 * It describes the mandatory members all elements throughout the workflow engine
 * have in common.
 * This interface is part of all elements, used within the workflows. This excludes
 * the workflow instance itself.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
interface ilWorkflowEngineElement
{
    /**
     * This method returns the context of the element. Due to the hierarchical
     * structure of the workflow engine, this is a reference to the parent object.
     * Using this, the caller can traverse through the tree of elements.
     *
     * @return ilWorkflowEngineElement Reference to a workflow engine element.
     */
    public function getContext();

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function setName($name);

    /**
     * @return mixed
     */
    public function getName();
}
