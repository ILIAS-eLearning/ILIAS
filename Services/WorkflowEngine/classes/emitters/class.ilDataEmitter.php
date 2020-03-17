<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilEmitter.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilDetector.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilNode.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilWorkflowEngineElement.php';

/**
 * ilDataEmitter is part of the petri net based workflow engine.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilDataEmitter implements ilEmitter, ilWorkflowEngineElement
{
    /**
     * This holds a reference to the detector, which is to be triggered.
     *
     * @var ilDetector
     */
    private $target_detector;

    /**
     * This holds a reference to the parent ilNode.
     *
     * @var ilNode
     */
    private $context;

    /** @var bool $emitted Holds information if the emitter emitted at least once. */
    private $emitted;

    /** @var string $name */
    protected $name;

    /** @var string $var_name */
    protected $var_name;

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
    public function setTargetDetector(ilDetector $target_detector)
    {
        $this->target_detector = $target_detector;
    }

    /**
     * Gets the currently set target detector of this emitter.
     *
     * @return ilDetector Reference to the target detector.
     */
    public function getTargetDetector()
    {
        return $this->target_detector;
    }

    /**
     * Returns a reference to the parent node of this emitter.
     *
     * @return ilNode Reference to the parent node.
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Executes this emitter after activating the target node.
     */
    public function emit()
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
            $this->target_detector->trigger(array());
        }
        $this->emitted = true;
    }

    /**
     * @return bool
     */
    public function getActivated()
    {
        return $this->emitted;
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

    /**
     * @return string
     */
    public function getVarName()
    {
        return $this->var_name;
    }

    /**
     * @param string $var_name
     */
    public function setVarName($var_name)
    {
        $this->var_name = $var_name;
    }
}
