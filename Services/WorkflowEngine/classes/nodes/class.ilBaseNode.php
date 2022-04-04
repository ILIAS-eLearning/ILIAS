<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilNode.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilEmitter.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilDetector.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilActivity.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilWorkflow.php';

/**
 * Class ilBaseNode
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
abstract class ilBaseNode implements ilNode
{
    /**
     * This holds a reference to the parent ilNode.
     *
     * @var ilNode|ilWorkflow
     */
    protected $context;

    /**
     * This holds an array of detectors attached to this node.
     *
     * @var \ilDetector Array if ilDetector
     */
    protected $detectors;

    /**
     * This holds an array of emitters attached to this node.
     *
     * @var \ilEmitter Array of ilEmitter
     */
    protected $emitters;

    /**
     * This holds an array of activities attached to this node.
     *
     * @var \ilActivity Array of ilActivity
     */
    protected $activities;

    /**
     * This holds the activation status of the node.
     *
     * @var boolean
     */
    protected bool $active = false;

    /** @var string $name */
    protected string $name;

    /** @var array $runtime_vars */
    protected array $runtime_vars;

    /**
     * This holds if the node represents a forward condition.
     *
     * The forward condition works like this:
     * The node itself transits to multiple nodes, which must represent intermediate events or nodes
     * that show such characteristics. If one the forward nodes is triggered, they need to look back and
     * instruct the node to deactivate all other outgoing forward flows so their event detectors are taken
     * down.
     *
     * @var boolean
     */
    public bool $is_forward_condition_node = false;

    /**
     * Adds a detector to the list of detectors.
     *
     * @param ilDetector $detector
     */
    public function addDetector(ilDetector $detector) : void
    {
        $this->detectors[] = $detector;
        $this->context->registerDetector($detector);
    }

    /**
     * Returns all currently set detectors
     */
    public function getDetectors()
    {
        return $this->detectors;
    }

    /**
     * Adds an emitter to the list of emitters.
     *
     * @param ilEmitter $emitter
     */
    public function addEmitter(ilEmitter $emitter, bool $else = false) : void
    {
        $this->emitters[] = $emitter;
    }

    /**
     * Returns all currently set emitters
      */
    public function getEmitters(bool $else = false)
    {
        return $this->emitters;
    }

    /**
     * Adds an activity to the list of activities.
     *
     * @param ilActivity $activity
     */
    public function addActivity(ilActivity $activity, bool $else = false) : void
    {
        $this->activities[] = $activity;
    }

    /**
     * Returns all currently set activities
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * Returns a reference to the parent workflow object.
     *
     * @return \ilWorkflow
     */
    public function getContext()
    {
        return $this->context;
    }

    public function setName($name) : void
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

    /**
     * @return array
     */
    public function getRuntimeVars() : array
    {
        return $this->runtime_vars;
    }

    /**
     * @param array $runtime_vars
     */
    public function setRuntimeVars(array $runtime_vars) : void
    {
        $this->runtime_vars = $runtime_vars;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getRuntimeVar(string $name) : array
    {
        return $this->runtime_vars[$name];
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setRuntimeVar(string $name, $value) : void
    {
        $this->runtime_vars[$name] = $value;
    }

    /**
     * Method called on activation of the node.
     *
     * @return void
     */
    public function onActivate() : void
    {
        return;
    }

    /**
     * Method calles on deactivation of the node.
     *
     * @return void
     */
    public function onDeactivate() : void
    {
        return;
    }

    /**
     * Returns the activation status of the node.
     *
     * @return boolean Activation status of the node.
     */
    public function isActive() : bool
    {
        return $this->active;
    }

    /**
     * @return mixed
     */
    abstract public function attemptTransition();

    /**
     * @return mixed
     */
    abstract public function checkTransitionPreconditions();

    /**
     * @return mixed
     */
    abstract public function executeTransition();

    /**
     * @return mixed
     */
    abstract public function activate();

    /**
     * @return mixed
     */
    abstract public function deactivate();

    /**
     * @param \ilDetector $detector
     *
     * @return mixed
     */
    abstract public function notifyDetectorSatisfaction(ilDetector $detector);
}
