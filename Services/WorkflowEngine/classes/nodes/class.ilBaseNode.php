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
     * @var ilNode
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
    protected $active = false;

    /** @var string $name */
    protected $name;

    /** @var array $runtime_vars */
    protected $runtime_vars;

    /**
     * Adds a detector to the list of detectors.
     *
     * @param ilDetector $detector
     */
    public function addDetector(ilDetector $detector)
    {
        $this->detectors[] = $detector;
        $this->context->registerDetector($detector);
    }

    /**
     * Returns all currently set detectors
     *
     * @return ilDetector[] Array with objects of ilDetector
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
    public function addEmitter(ilEmitter $emitter)
    {
        $this->emitters[] = $emitter;
    }

    /**
     * Returns all currently set emitters
     *
     * @return ilEmitter[] Array with objects of ilEmitter
     */
    public function getEmitters()
    {
        return $this->emitters;
    }

    /**
     * Adds an activity to the list of activities.
     *
     * @param ilActivity $activity
     */
    public function addActivity(ilActivity $activity)
    {
        $this->activities[] = $activity;
    }

    /**
     * Returns all currently set activities
     *
     * @return ilActivity[] Array with objects of ilActivity
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

    /***
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
     * @return array
     */
    public function getRuntimeVars()
    {
        return $this->runtime_vars;
    }

    /**
     * @param array $runtime_vars
     */
    public function setRuntimeVars($runtime_vars)
    {
        $this->runtime_vars = $runtime_vars;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getRuntimeVar($name)
    {
        return $this->runtime_vars[$name];
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setRuntimeVar($name, $value)
    {
        $this->runtime_vars[$name] = $value;
    }

    /**
     * Method called on activation of the node.
     *
     * @return void
     */
    public function onActivate()
    {
        return;
    }

    /**
     * Method calles on deactivation of the node.
     *
     * @return void
     */
    public function onDeactivate()
    {
        return;
    }

    /**
     * Returns the activation status of the node.
     *
     * @return boolean Activation status of the node.
     */
    public function isActive()
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
