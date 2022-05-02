<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Case node of the petri net based workflow engine.
 *
 * The case node is a deciding node. It features a multiple set of emitters
 * and no activities.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilCaseNode extends ilBaseNode
{
    /** @var bool $is_exclusive_join */
    private bool $is_exclusive_join = false;

    /** @var bool $is_exclusive_fork */
    private bool $is_exclusive_fork = false;

    /** @var ilEmitter[] $else_emitters */
    public array $else_emitters;

    /** @var bool $is_exclusive */
    public bool $is_exclusive;

    private array $condition_emitter_pairs = [];

    /**
     * Default constructor.
     *
     * @param ilWorkflow $context Reference to the parent workflow.
     */
    public function __construct(ilWorkflow $context)
    {
        $this->context = $context;
        $this->detectors = array();
        $this->emitters = array();
        $this->else_emitters = array();
        $this->activities = array();
        $this->is_exclusive = false;
    }

    public function setIsExclusiveJoin($is_exclusive) : void
    {
        $this->is_exclusive_join = $is_exclusive;
    }

    public function setIsExclusiveFork($is_exclusive) : void
    {
        $this->is_exclusive_fork = $is_exclusive;
    }

    /**
     * Activates the node.
     */
    public function activate()
    {
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
     * Please note, that in a conditional node, this means the node can transit
     * to one or another outcome. This method only returns false, if the return
     * value of the method is neither true nor false.
     *
     * @return boolean True, if node is ready to transit.
     */
    public function checkTransitionPreconditions() : bool
    {
        // queries the $detectors if their conditions are met.
        $isPreconditionMet = true;
        foreach ($this->detectors as $detector) {
            if ($isPreconditionMet === true) {
                $isPreconditionMet = $detector->getDetectorState();
                if ($isPreconditionMet && ($this->is_exclusive_join || $this->is_exclusive_fork || $this->is_exclusive)) {
                    break;
                }
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
    public function attemptTransition() : bool
    {
        if ($this->checkTransitionPreconditions() === true) {
            $this->executeTransition();
            return true;
        }

        return false;
    }

    /**
     * Executes the 'then'-transition of the node.
     */
    public function executeTransition()
    {
        $this->deactivate();
        if (count($this->activities) !== 0) {
            foreach ($this->activities as $activity) {
                $activity->execute();
            }
        }

        foreach ($this->condition_emitter_pairs as $pair) {
            $eval_function = static function ($that) use ($pair) {
                return eval($pair['expression']);
            };

            if ($eval_function($this->detectors) === true) {
                $emitter = $pair['emitter'];
                $emitter->emit();
                if ($this->is_exclusive_fork || $this->is_exclusive_join) {
                    return;
                }
            }
        }
    }

    /**
     * Adds an emitter to one of the lists attached to the node.
     *
     * @param ilEmitter $emitter
     * @param boolean   $else True, if the emitter should be an 'else'-emitter.
     */
    public function addEmitter(ilEmitter $emitter, $else = 'return true;') : void
    {
        $this->condition_emitter_pairs[] = array(
            'emitter' => $emitter,
            'expression' => $else
        );
    }

    /**
     * This method is called by detectors, that just switched to being satisfied.
     *
     * @param ilDetector $detector ilDetector which is now satisfied.
     *
     * @return mixed|void
     */
    public function notifyDetectorSatisfaction(ilDetector $detector) : void
    {
        if ($this->isActive()) {
            $this->attemptTransition();
        }
    }
}
