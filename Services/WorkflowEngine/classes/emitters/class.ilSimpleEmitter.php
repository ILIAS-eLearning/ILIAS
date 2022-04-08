<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilSimpleEmitter is part of the petri net based workflow engine.
 *
 * The simple emitter is the internal signals yeoman, doing nothing but triggering
 * the designated simple detector.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilSimpleEmitter implements ilEmitter, ilWorkflowEngineElement
{
    /**
     * This holds a reference to the detector, which is to be triggered.
     *
     * @var ilDetector
     */
    private ilDetector $target_detector;

    /**
     * This holds a reference to the parent ilNode.
     *
     * @var ilNode
     */
    private ilNode $context;

    /** @var string $name */
    protected string $name;

    /**
     * Default constructor.
     *
     * @param ilNode $context Reference to the parent node.
     */
    public function __construct(ilNode $context)
    {
        $this->context = $context;
    }

    /**
     * Sets the target detector for this emitter.
     *
     * @param ilDetector $target_detector
     */
    public function setTargetDetector(ilDetector $target_detector) : void
    {
        $this->target_detector = $target_detector;
    }

    /**
     * Gets the currently set target detector of this emitter.
     *
     * @return ilDetector Reference to the target detector.
     */
    public function getTargetDetector() : ilDetector
    {
        return $this->target_detector;
    }

    /**
     * Returns a reference to the parent node of this emitter.
     *
     * @return ilNode Reference to the parent node.
     */
    public function getContext() : ilNode
    {
        return $this->context;
    }

    /**
     * Executes this emitter.
     */
    public function emit() : void
    {
        $this->target_detector->trigger(array());
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
}
