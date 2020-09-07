<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';

/**
 * Class ilCounterDetector
 *
 * This detector has to be triggered n times before it is satisfied. It is used
 * in looping controls and the like. This detector must not be used as external
 * detector.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilCounterDetector extends ilSimpleDetector
{
    /**
     * This holds the number of trigger events before the detector is satisfied.
     *
     * @var integer Number of trigger events expected.
     */
    private $expected_trigger_events = 1;

    /**
     * This holds the current number of trigger events which have already taken
     * place.
     *
     * @var integer Number of past trigger events.
     */
    private $actual_trigger_events = 0;

    /**
     * Default constructor, passing the context to the parent constructor.
     *
     * @param ilNode $context
     */
    public function __construct(ilNode $context)
    {
        parent::__construct($context);
    }

    /**
     * Set the expected trigger event count before the detector is satisfied.
     *
     * @param integer $count
     */
    public function setExpectedTriggerEvents($count)
    {
        $this->expected_trigger_events = (int) $count;
    }

    /**
     * Returns the currently set number of expected trigger events.
     *
     * @return integer
     */
    public function getExpectedTriggerEvents()
    {
        return $this->expected_trigger_events;
    }

    /**
     * Returns the actual trigger events of the detector.
     *
     * @return integer Number of past trigger events.
     *
     */
    public function getActualTriggerEvents()
    {
        return $this->actual_trigger_events;
    }

    /**
     * Sets the actual count of trigger events already taken place.
     *
     * Reason this method exists, is to allow the workflow controller to
     * "fast forward" workflows to set a non-default state. I.e. a workflow
     * has to be set into a state in the middle of running. Use with care.
     *
     * @param integer $count Number of past trigger events.
     *
     */
    public function setActualTriggerEvents($count)
    {
        if ($this->expected_trigger_events < (int) $count) {
            $this->actual_trigger_events = $count;
        } else {
            // TODO: throw actual must be smaller than expected.
        }
    }

    /**
     * Trigger this detector. Params are an array. These are part of the interface
     * but ignored here.
     *
     * @todo Handle ignored $a_params.
     *
     * @param array $params
     *
     * @return boolean False, if detector was already satisfied before.
     */
    public function trigger($params)
    {
        if ($this->actual_trigger_events < $this->expected_trigger_events) {
            $this->actual_trigger_events++;
            if ($this->actual_trigger_events == $this->expected_trigger_events) {
                $this->setDetectorState(true);
            }
            return true;
        }
        return false;
    }
}
