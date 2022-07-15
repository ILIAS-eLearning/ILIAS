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
 * ilDataEmitter is part of the petri net based workflow engine.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilDataEmitter implements ilEmitter, ilWorkflowEngineElement
{
    /**
     * This holds a reference to the detector, which is to be triggered.
     */
    private ?ilDetector $target_detector = null;

    /**
     * This holds a reference to the parent ilNode.
     */
    private ?ilNode $context = null;

    /**
     * Holds information if the emitter emitted at least once.
     */
    private bool $emitted;

    protected $name;

    protected string $var_name = '';

    /**
     * Default constructor.
     *
     * @param ilNode $context Reference to the parent node.
     */
    public function __construct(ilNode $context)
    {
        $this->context = $context;
        $this->emitted = false;
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
     */
    public function getTargetDetector() : ?ilDetector
    {
        return $this->target_detector;
    }

    /**
     * Returns a reference to the parent node of this emitter.
     *
     * @return ilNode Reference to the parent node.
     */
    public function getContext() : ?ilNode
    {
        return $this->context;
    }

    /**
     * Executes this emitter after activating the target node.
     */
    public function emit() : void
    {
        $instance_vars = $this->context->getContext()->getInstanceVars();

        $target = $this->var_name;
        foreach ($instance_vars as $instance_var) {
            if ($instance_var['id'] == $this->var_name) {
                if ($instance_var['reference']) {
                    $target = $instance_var['target'];
                }
            }
        }

        foreach ((array) $this->context->getContext()->getInstanceVars() as $value) {
            if ($value['id'] == $target) {
                $this->getContext()->getContext()->setInstanceVarById($target, $value['value']);
            }
        }

        if ($this->target_detector instanceof ilDetector) {
            $this->target_detector->trigger([]);
        }
        $this->emitted = true;
    }

    public function getActivated() : bool
    {
        return $this->emitted;
    }

    public function setName($name) : void
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getVarName() : string
    {
        return $this->var_name;
    }

    public function setVarName(string $var_name) : void
    {
        $this->var_name = $var_name;
    }
}
