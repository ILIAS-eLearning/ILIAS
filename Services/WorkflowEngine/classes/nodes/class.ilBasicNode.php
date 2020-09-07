<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/nodes/class.ilBaseNode.php';

/**
 * Workflow Node of the petri net based workflow engine.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilBasicNode extends ilBaseNode
{
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
    private $is_forward_condition_node;

    /** @var bool $is_forward_condition_event*/
    public $is_forward_condition_event;

    /** @var string $ident */
    public $ident;

    /**
     * Default constructor.
     *
     * @param ilWorkflow $context Reference to the workflow the node is attached to.
     */
    public function __construct(ilWorkflow $context)
    {
        $this->context = $context;
        $this->detectors = array();
        $this->emitters = array();
        $this->activities = array();
        $this->active = false;
        $this->is_forward_condition_node = false;
        $this->is_forward_condition_event = false;
        $this->ident = strtoupper(substr(md5(spl_object_hash($this)), 0, 6));
    }

    /**
     * Activates the node.
     */
    public function activate()
    {
        if ($this->isActive()) {
            return;
        }
        
        $this->active = true;

        foreach ($this->detectors as $detector) {
            $detector->onActivate();
        }
        $this->onActivate();
        $this->attemptTransition();
    }

    /**
     * Deactivates the node.
     */
    public function deactivate()
    {
        $this->active = false;
        foreach ($this->detectors as $detector) {
            $detector->onDeactivate();
        }
        $this->onDeactivate();
    }

    /**
     * Checks, if the preconditions of the node to transit are met.
     *
     * @return boolean True, if node is ready to transit.
     */
    public function checkTransitionPreconditions()
    {
        // queries the $detectors if their conditions are met.
        $isPreconditionMet = true;
        foreach ($this->detectors as $detector) {
            if ($isPreconditionMet == true) {
                $isPreconditionMet = $detector->getDetectorState();
            }
        }
        return $isPreconditionMet;
    }

    /**
     * Attempts to transit the node.
     *
     * Basically, this checks for preconditions and transits, returning true or
     * false if preconditions are not met, aka detectors are not fully satisfied.
     *
     * @return boolean True, if transition succeeded.
     */
    public function attemptTransition()
    {
        if ($this->checkTransitionPreconditions() == true) {
            $this->executeTransition();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Executes all attached activities.
     */
    private function executeActivities()
    {
        if (count($this->activities) != 0) {
            foreach ($this->activities as $activity) {
                $activity->execute();
            }
        }
    }

    /**
     * Executes all attached emitters.
     */
    private function executeEmitters()
    {
        if (count($this->emitters) != 0) {
            foreach ($this->emitters as $emitter) {
                $emitter->emit();
            }
        }
    }

    /**
     * Executes the transition, calls all activities and emitters to execute.
     */
    public function executeTransition()
    {
        $this->deactivate();
        $this->executeActivities();
        $this->pingbackToPredecessorNodes();
        $this->executeEmitters();
    }

    /**
     * This method is called by detectors, that just switched to being satisfied.
     *
     * @param ilDetector $detector ilDetector which is now satisfied.
     *
     * @return mixed|void
     */
    public function notifyDetectorSatisfaction(ilDetector $detector)
    {
        if ($this->isActive()) {
            $this->attemptTransition();
        }
    }

    /**
     * @return boolean
     */
    public function isForwardConditionNode()
    {
        return $this->is_forward_condition_node;
    }

    /**
     * @param boolean $is_forward_condition_node
     */
    public function setIsForwardConditionNode($is_forward_condition_node)
    {
        $this->is_forward_condition_node = $is_forward_condition_node;
    }

    /**
     * Deactivates all forward condition nodes except for the given one.
     *
     * @see is_forward_condition_node for how this thing works.
     *
     * @param ilNode $activated_node
     */
    public function deactivateForwardConditionNodes(ilNode $activated_node)
    {
        if ($this->is_forward_condition_node) {
            foreach ($this->emitters as $emitter) {
                /** @var ilSimpleEmitter $emitter */
                $target_detector = $emitter->getTargetDetector();

                /** @var ilWorkflowEngineElement $target_node */
                $target_node = $target_detector->getContext();

                if ($target_node === $activated_node) {
                    continue;
                }
                $target_node->deactivate();
            }
        }
    }

    public function pingbackToPredecessorNodes()
    {
        /** @var ilSimpleDetector $detector */
        foreach ($this->detectors as $detector) {
            /** @var ilBasicNode $source_node */
            $source_node = $detector->getSourceNode();
            if ($source_node && $source_node->is_forward_condition_node) {
                $source_node->deactivateForwardConditionNodes($this);
            }
        }
    }
}
