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
 * ilTimerDetector is part of the petri net based workflow engine.
 *
 * This detector implements a timer-feature. It has a start (date)time and a
 * time limit.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilTimerDetector extends ilSimpleDetector implements ilExternalDetector
{
    /**
     * Holds the type of the event to listen for.
     * In case of this detector class, it is set up to listen to a default
     * 'time passed' event. It has no means of modifying it.
     * @see class.ilEventDetector for detailed information on these values.
     */

    private string $event_type = 'time_passed';
    private string $event_content = 'time_passed';
    private string $event_subject_type = 'none';
    private string $event_subject_identifier = '0';
    private string $event_context_type = 'none';
    private string $event_context_identifier = '0';
    private bool $timer_relative;

    /**
     * Timestamp of the start of the timer.
     *
     * @var int  Unix timestamp
     */
    private int $timer_start = 0;

    /**
     * Limit of the timer to run.
     *
     * @var int Seconds to determine the timers runtime.
     */
    private int $timer_limit = 0;

    /**
     * This holds the start of the listening period.
     * @var int Unix timestamp, start of listening period.
     */
    private int $listening_start = 0;

    /**
     * This holds the end of the listening period.
     * @var int Unix timestamp, end of listening period.
     */
    private int $listening_end = 0;

    /**
     * This holds the database id of the detector, if set, or null.
     *
     * @var int Database Id of the detector
     */
    private ?int $db_id = null;

    /**
     * Sets the timers start datetime.
     * @param int $timer_start Unix timestamp.
     */
    public function setTimerStart(int $timer_start) : void
    {
        $this->timer_start = $timer_start;
    }

    /**
     * Returns the currently set timer start.
     *
     * @return int Unix timestamp of the timers start.
     */
    public function getTimerStart() : int
    {
        return $this->timer_start;
    }

    /**
     * Sets the timers limit
     * @param int $timer_limit Seconds of the timers runtime.
     */
    public function setTimerLimit(int $timer_limit) : void
    {
        $this->timer_limit = $timer_limit;
    }

    /**
     * Returns the currently set timers limit.
     *
     * @return int Seconds of the timers limit.
     */
    public function getTimerLimit() : int
    {
        return $this->timer_limit;
    }

    /**
     * Trigger this detector. Params are an array. These are part of the interface
     * but ignored here.
     *
     * @todo Handle ignored $params.
     *
     * @param array $params
     *
     * @return bool False, if detector was already satisfied before.
     */
    public function trigger($params) : ?bool
    {
        if ($this->getDetectorState() === true) {
            return false;
        }

        if ($this->timer_limit + $this->timer_start <= ilWorkflowUtils::time()) {
            $this->setDetectorState(true);
        }
        return true;
    }

    /**
     * Returns if the detector is currently listening.
     *
     * @return bool
     */
    public function isListening() : bool
    {
        // No listening phase = always listening.
        if ($this->listening_start === 0 && $this->listening_end === 0) {
            return true;
        }

        // Listening started?
        if ($this->listening_start < ilWorkflowUtils::time()) {
            // Listening not ended or infinite?
            if ($this->listening_end === 0 || $this->listening_end > ilWorkflowUtils::time()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets the timeframe, in which the detector is listening.
     * @param int $listening_start Unix timestamp start of listening period.
     * @param int $listening_end   Unix timestamp end of listening period.
     * @throws ilWorkflowInvalidArgumentException
     */
    public function setListeningTimeframe(int $listening_start, int $listening_end)
    {
        $this->listening_start = $listening_start;

        if ($this->listening_start > $listening_end && $listening_end !== 0) {
            throw new ilWorkflowInvalidArgumentException('Listening timeframe is (start vs. end) is invalid.');
        }

        $this->listening_end = $listening_end;
    }

    /**
     * Method called on activation.
     */
    public function onActivate() : void
    {
        if ($this->timer_relative) {
            if ($this->timer_start === 0) {
                $this->listening_start = time();
            } else {
                $this->listening_start = $this->timer_start;
            }
            if ($this->timer_limit !== 0) {
                $this->listening_end = $this->listening_start + $this->timer_limit;
            } else {
                $this->listening_end = 0;
            }
        }
        $this->setDetectorState(false);
        $this->writeDetectorToDb();
    }

    /**
     * Method called on deactivation.
     */
    public function onDeactivate() : void
    {
        $this->setDetectorState(false);
        $this->deleteDetectorFromDb();
    }

    public function setDbId(?int $a_id) : void
    {
        $this->db_id = $a_id;
    }

    /**
     * Returns the database id of the detector if set.
     *
     * @return int
     */
    public function getDbId() : int
    {
        if ($this->db_id !== null) {
            return $this->db_id;
        }

        throw new ilWorkflowObjectStateException('No database ID set.');
    }

    /**
     * Returns, if the detector has a database id.
     *
     * @return bool If a database id is set.
     */
    public function hasDbId() : bool
    {
        return $this->db_id !== null;
    }

    /**
     * Passes this detector to the ilWorkflowDBHelper in order to write or update
     * the detector data to the database.
     */
    public function writeDetectorToDb() : void
    {
        ilWorkflowDbHelper::writeDetector($this);
    }

    /**
     * Passes this detector to the ilWorkflowDbHelper in order to remove the
     * detector data from the database.
     */
    public function deleteDetectorFromDb() : void
    {
        ilWorkflowDbHelper::deleteDetector($this);
    }

    /**
     * Returns the event type and content currently set to the detector.
     *
     * @return array{type: string, content: string}
     */
    public function getEvent() : array
    {
        return ['type' => $this->event_type, 'content' => $this->event_content];
    }

    /**
     * Get the event subject set to the detector.
     *
     * @return array{type: string, identifier: string}
     */
    public function getEventSubject() : array
    {
        return ['type' => $this->event_subject_type, 'identifier' => $this->event_subject_identifier];
    }

    /**
     * Get the event context set to the detector.
     *
     * @return array{type: string, identifier: string}
     */
    public function getEventContext() : array
    {
        return ['type' => $this->event_context_type, 'identifier' => $this->event_context_identifier];
    }

    /**
     * Returns the listening timefrage of the detector.
     * @return array{listening_start: int, listening_end: int}
     */
    public function getListeningTimeframe() : array
    {
        return ['listening_start' => $this->listening_start, 'listening_end' => $this->listening_end];
    }

    public function isTimerRelative() : bool
    {
        return $this->timer_relative;
    }

    public function setTimerRelative(bool $timer_relative) : void
    {
        $this->timer_relative = $timer_relative;
    }
}
