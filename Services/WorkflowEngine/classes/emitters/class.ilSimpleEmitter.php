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

/**
 * ilSimpleEmitter is part of the petri net based workflow engine.
 *
 * The simple emitter is the internal signals yeoman, doing nothing but triggering
 * the designated simple detector.
 *
 * @author Maximilian Becker <mbecker@databay.de>
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
        $this->target_detector->trigger([]);
    }

    public function setName( ?string $name) : void
    {
        $this->name = $name;
    }

    public function getName() : ?string
    {
        return $this->name;
    }
}
