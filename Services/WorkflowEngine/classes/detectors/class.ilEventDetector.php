<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/classes/detectors/class.ilSimpleDetector.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/WorkflowEngine/interfaces/ilExternalDetector.php';

/**
 * ilEventDetector is part of the petri net based workflow engine.
 *
 * The event detector listens to non-timer related outside events. Examples are
 * events raised by Ilias, such as a new member joined a group/course, a test
 * was finished, a learning progress was updated, etc. Please note here the
 * differing handling of the params at the trigger method.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilEventDetector extends ilSimpleDetector implements ilExternalDetector
{
    /**
     * Holds the type of the event to listen for.
     *
     * This is a text 'name' of the event.
     * e.g. 'course_event', 'course_join', 'test_event', 'test_finished'...
     * Atm it's unclear what I'll be getting here, this struct should be
     * flexible enough to handle most ideas my colleagues may come up with.
     *
     * The following fields will end up as columns in the database, allowing the
     * workflow controller to quickly find the workflow/s, which has/have event
     * detectors wired up to listen to the event just raised. This is performance
     * critical and so already taken of early in the development.
     *
     * @var string Name of type of the event to be listened for.
     */
    private $event_type;

    /**
     * Holds the content of the event to listen to.
     *
     * This is a second 'qualifier'. Let's say we do not get something nice
     * like 'user_joined_course_as_member' as $event_type but something opaque
     * like 'course_even', then this second qualifier allows to further precise
     * the needed event. This is necessary to minimize workload for the workflow
     * controllers event handler. Premature optimization you yell? Damn right!
     *
     * @var string Content of the event, nature, second qualifier.
     */
    private $event_content;

    /**
     * Holding the subject type of the event to be listened for.
     *
     * After knowing the 'what', we want to know the 'who'. Using a subject type,
     * 'who' is not limited to actual people but can be anything.
     *
     * @var string Name of the subject type.
     */
    private $event_subject_type;

    /**
     * This is the actual identifier of the 'who'. If subject_type is a usr, this
     * is a usr_id. If subject_type is a grp, this is a group_id. (or  group ref id)
     *
     * @var integer Identifier of the events subject.
     *
     */
    private $event_subject_identifier;

    /**
     * Type of the event context.
     *
     * Now we turn to the 'where' of things, completing the generic definition
     * of the event to listen for. The event context type _may_ be implicit part
     * of the definition (e.g. due to a 'crs_member_joined' event type), but it
     * doesn't have to. Example here is a crs as type.
     *
     * @var string Type if the events context type.
     */
    private $event_context_type;

    /**
     * Identifier of the events context.
     *
     * This can be a course_ref_id, when the context_type is crs or the like.
     *
     * @var integer Identifier of the events context.
     */
    private $event_context_identifier;

    /**
     * Holds the start of the listening period.
     * @var integer Unix timestamp, start of listening period.
     */
    private $listening_start = 0;

    /**
     * Holds the end of the listening period.
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
     *Sets the event type and content (/qualifier) for the detector. 'WHAT'
     *
     * @param string $event_type
     * @param string $event_content
     */
    public function setEvent($event_type, $event_content)
    {
        $this->event_type = (string) $event_type;
        $this->event_content = (string) $event_content;
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
     * Set the event subject type to the detector. 'WHO'
     *
     * @param string  $event_subject_type
     * @param integer $event_subject_identifier
     */
    public function setEventSubject($event_subject_type, $event_subject_identifier)
    {
        $this->event_subject_type = (string) $event_subject_type;
        $this->event_subject_identifier = $event_subject_identifier;
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
     * Set the event context to the detector. 'WHERE' / 'ON WHAT' / 'ON WHOM'
     *
     * @param string  $event_context_type
     * @param integer $event_context_identifier
     */
    public function setEventContext($event_context_type, $event_context_identifier)
    {
        $this->event_context_type = (string) $event_context_type;
        $this->event_context_identifier = $event_context_identifier;
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
     * Triggers the detector.
     *
     * The params for this trigger have to be assembled like this:
     * array (
     *   'event_type'                  => 'course_join',
     *  'event_content'                => 'member_join',
     *  'event_subject_type            => 'usr',
     *  'event_subject_identifier'     => '6',
     *  'event_context_type'           => 'crs',
     *  'event_context_identifier'     => '48'
     * )
     *
     * This would describe an event, in which a user joined a course as member.
     * If an identifier is '0', they are meant as 'for all'.
     *
     * @param array $params Associative array with params, see docs for details.
     *
     * @return bool|void
     */
    public function trigger($params)
    {
        if (!$this->isListening()) {
            return;
        }

        if ($this->event_type !== $params[0]) {
            // Wrong event type -> no action here.
            return;
        }

        if ($this->event_content !== $params[1]) {
            // Wrong event content -> no action here.
            return;
        }

        if ($this->event_subject_type !== $params[2]) {
            // Wrong event subject type -> no action here.
            return;
        }

        if ($this->event_subject_identifier !== $params[3] && $this->event_subject_identifier != 0) {
            // Wrong event subject identifier and identifier here not 0 (not *all*) -> no action.
            return;
        }
        
        if ($this->event_context_type !== $params[4]) {
            // Wrong event context type -> no action.
            return;
        }

        if ($this->event_context_identifier !== $params[5] && $this->event_context_identifier != 0) {
            // Wrong event context identifier and identifier here not 0 (not *all*) -> no action.
            return;
        }

        // We're through checks now, let's see if this detector is already satisfied.
        if ($this->getDetectorState() == false) {
            // X -> ilNode     -> ilWorkflow -> Method...
            foreach ($params as $key => $value) {
                $this->getContext()->setRuntimeVar($key, $value);
            }
            $this->getContext()->setRuntimeVar('current_event', $params);
            $this->was_activated = true;
            $this->setDetectorState(true);
            return true;
        }

        return false;
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
        if ($this->listening_start <= ilWorkflowUtils::time()) {
            // Listening not ended or infinite?
            if ($this->listening_end >= ilWorkflowUtils::time()
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

        if ($this->listening_start > $listening_end && $listening_end != 0) {
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
     * @return int
     * @throws \ilWorkflowObjectStateException
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
     * Returns the listening timefrage of the detector.
     *
     * @return array array ('listening_start' => $this->listening_start, 'listening_end' => $this->listening_end)
     */
    public function getListeningTimeframe()
    {
        return array('listening_start' => $this->listening_start, 'listening_end' => $this->listening_end);
    }

    /** @var bool $was_activated */
    public $was_activated;

    /**
     * @return bool
     */
    public function getActivated()
    {
        return $this->was_activated;
    }
}
