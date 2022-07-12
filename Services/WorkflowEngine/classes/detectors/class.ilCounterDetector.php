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
 * Class ilCounterDetector
 *
 * This detector has to be triggered n times before it is satisfied. It is used
 * in looping controls and the like. This detector must not be used as external
 * detector.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilCounterDetector extends ilSimpleDetector
{
    /**
     * This holds the number of trigger events before the detector is satisfied.
     */
    private int $expected_trigger_events = 1;

    /**
     * This holds the current number of trigger events which have already taken
     * place.
     */
    private int $actual_trigger_events = 0;

    /**
     * Set the expected trigger event count before the detector is satisfied.
     *
     * @param int $count
     */
    public function setExpectedTriggerEvents(int $count) : void
    {
        $this->expected_trigger_events = $count;
    }

    /**
     * Returns the currently set number of expected trigger events.
     *
     * @return int
     */
    public function getExpectedTriggerEvents() : int
    {
        return $this->expected_trigger_events;
    }

    /**
     * Returns the actual trigger events of the detector.
     *
     * @return int Number of past trigger events.
     *
     */
    public function getActualTriggerEvents() : int
    {
        return $this->actual_trigger_events;
    }

    /**
     * Sets the actual count of trigger events already taken place.
     * Reason this method exists, is to allow the workflow controller to
     * "fast forward" workflows to set a non-default state. I.e. a workflow
     * has to be set into a state in the middle of running. Use with care.
     * @param int $count Number of past trigger events.
     */
    public function setActualTriggerEvents(int $count) : void
    {
        if ($this->expected_trigger_events < $count) {
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
     * @return bool False, if detector was already satisfied before.
     */
    public function trigger($params) : ?bool
    {
        if ($this->actual_trigger_events < $this->expected_trigger_events) {
            $this->actual_trigger_events++;
            if ($this->actual_trigger_events === $this->expected_trigger_events) {
                $this->setDetectorState(true);
            }
            return true;
        }
        return false;
    }
}
