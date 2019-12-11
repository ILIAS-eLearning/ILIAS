<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilExternalDetector.php';

/**
 * ilTimerDetector is part of the petri net based workflow engine.
 *
 * This detector implements a timer-feature. It has a start (date)time and a
 * time limit.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
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

    /** @var string $event_type */
    private $event_type					= 'time_passed';

    /** @var string $event_content */
    private $event_content				= 'time_passed';

    /** @var string $event_subject_type */
    private $event_subject_type			= 'none';

    /** @var string $event_subject_identifier SIC! */
    private $event_subject_identifier	= '0';

    /** @var string $event_context_type */
    private $event_context_type			= 'none';

    /** @var string $event_context_identifier SIC! */
    private $event_context_identifier	= '0';

    /** @var bool $relative_timer */
    private $timer_relative;

    /**
     * Timestamp of the start of the timer.
     *
     * @var integer  Unix timestamp
     */
    private $timer_start = 0;

    /**
     * Limit of the timer to run.
     *
     * @var integer Seconds to determine the timers runtime.
     */
    private $timer_limit = 0;

    /**
     * This holds the start of the listening period.
     * @var integer Unix timestamp, start of listening period.
     */
    private $listening_start = 0;

    /**
     * This holds the end of the listening period.
     * @var integer Unix timestamp, end of listening period.
     */
    private $listening_end = 0;

    /**
     * This holds the database id of the detector, if set, or null.
     *
     * @var integer Database Id of the detector
     */
    private $db_id = null;

    /**
     * Default constructor, passing the context to the parent constructor.
     *
     * @param ilNode $context
     */
    public function __construct($context)
    {
        parent::__construct($context);
    }

    /**
     * Sets the timers start datetime.
     *
     * @param integer $timer_start Unix timestamp.
     */
    public function setTimerStart($timer_start)
    {
        $this->timer_start = (int) $timer_start;
    }

    /**
     * Returns the currently set timer start.
     *
     * @return integer Unix timestamp of the timers start.
     */
    public function getTimerStart()
    {
        return $this->timer_start;
    }

    /**
     * Sets the timers limit
     *
     * @param integer $timer_limit Seconds of the timers runtime.
     */
    public function setTimerLimit($timer_limit)
    {
        $this->timer_limit = (int) $timer_limit;
    }

    /**
     * Returns the currently set timers limit.
     *
     * @return integer Seconds of the timers limit.
     */
    public function getTimerLimit()
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
     * @return boolean False, if detector was already satisfied before.
     */
    public function trigger($params)
    {
        if ($this->getDetectorState() == true) {
            return false;
        }

        require_once './Services/WorkflowEngine/classes/utils/class.ilWorkflowUtils.php';
        if ($this->timer_limit + $this->timer_start <= ilWorkflowUtils::time()) {
            $this->setDetectorState(true);
        }
        return true;
    }

    /**
     * Returns if the detector is currently listening.
     *
     * @return boolean
     */
    public function isListening()
    {
        // No listening phase = always listening.
        if ($this->listening_start == 0 && $this->listening_end == 0) {
            return true;
        }

        // Listening started?
        require_once './Services/WorkflowEngine/classes/utils/class.ilWorkflowUtils.php';
        if ($this->listening_start < ilWorkflowUtils::time()) {
            // Listening not ended or infinite?
            if ($this->listening_end > ilWorkflowUtils::time()
                || $this->listening_end == 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets the timeframe, in which the detector is listening.
     *
     * @param integer $listening_start Unix timestamp start of listening period.
     * @param integer $listening_end   Unix timestamp end of listening period.
     *
     * @throws \ilWorkflowInvalidArgumentException
     */
    public function setListeningTimeframe($listening_start, $listening_end)
    {
        $this->listening_start = $listening_start;

        if ($this->listening_start > $listening_end  && $listening_end != 0) {
            require_once './Services/WorkflowEngine/exceptions/ilWorkflowInvalidArgumentException.php';
            throw new ilWorkflowInvalidArgumentException('Listening timeframe is (start vs. end) is invalid.');
        }

        $this->listening_end = $listening_end;
    }

    /**
     * Method called on activation.
     */
    public function onActivate()
    {
        if ($this->timer_relative) {
            if ($this->timer_start == 0) {
                $this->listening_start = time();
            } else {
                $this->listening_start = $this->timer_start;
            }
            if ($this->timer_limit != 0) {
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
    public function onDeactivate()
    {
        $this->setDetectorState(false);
        $this->deleteDetectorFromDb();
    }

    /**
     * Sets the database id of the detector.
     *
     * @param integer $a_id
     */
    public function setDbId($a_id)
    {
        $this->db_id = $a_id;
    }

    /**
     * Returns the database id of the detector if set.
     *
     * @return integer
     */
    public function getDbId()
    {
        if ($this->db_id != null) {
            return $this->db_id;
        } else {
            require_once './Services/WorkflowEngine/exceptions/ilWorkflowObjectStateException.php';
            throw new ilWorkflowObjectStateException('No database ID set.');
        }
    }

    /**
     * Returns, if the detector has a database id.
     *
     * @return boolean If a database id is set.
     */
    public function hasDbId()
    {
        if ($this->db_id == null) {
            return false;
        }

        return true;
    }

    /**
     * Passes this detector to the ilWorkflowDBHelper in order to write or update
     * the detector data to the database.
     */
    public function writeDetectorToDb()
    {
        require_once './Services/WorkflowEngine/classes/utils/class.ilWorkflowDbHelper.php';
        ilWorkflowDbHelper::writeDetector($this);
    }

    /**
     * Passes this detector to the ilWorkflowDbHelper in order to remove the
     * detector data from the database.
     */
    public function deleteDetectorFromDb()
    {
        require_once './Services/WorkflowEngine/classes/utils/class.ilWorkflowDbHelper.php';
        ilWorkflowDbHelper::deleteDetector($this);
    }

    /**
     * Returns the event type and content currently set to the detector.
     *
     * @return  array array('type' => $this->event_type, 'content' => $this->event_content)
     */
    public function getEvent()
    {
        return array('type' => $this->event_type, 'content' => $this->event_content);
    }

    /**
     * Get the event subject set to the detector.
     *
     * @return array array('type' => $this->event_subject_type, 'identifier' => $this->event_subject_identifier)
     */
    public function getEventSubject()
    {
        return array('type' => $this->event_subject_type, 'identifier' => $this->event_subject_identifier);
    }

    /**
     * Get the event context set to the detector.
     *
     * @return array array('type' => $this->event_context_type, 'identifier' => $this->event_context_identifier)
     */
    public function getEventContext()
    {
        return array('type' => $this->event_context_type, 'identifier' => $this->event_context_identifier);
    }

    /**
     * Returns the listening timefrage of the detector.
     * @return array array ('listening_start' => $this->listening_start, 'listening_end' => $this->listening_end)
     */
    public function getListeningTimeframe()
    {
        return array('listening_start' => $this->listening_start, 'listening_end' => $this->listening_end);
    }

    /**
     * @return bool
     */
    public function isTimerRelative()
    {
        return $this->timer_relative;
    }

    /**
     * @param bool $timer_relative
     */
    public function setTimerRelative($timer_relative)
    {
        $this->timer_relative = $timer_relative;
    }
}
