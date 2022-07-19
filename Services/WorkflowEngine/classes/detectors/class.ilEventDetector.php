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
 * ilEventDetector is part of the petri net based workflow engine.
 *
 * The event detector listens to non-timer related outside events. Examples are
 * events raised by Ilias, such as a new member joined a group/course, a test
 * was finished, a learning progress was updated, etc. Please note here the
 * differing handling of the params at the trigger method.
 *
 * @author Maximilian Becker <mbecker@databay.de>
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
     */
    private string $event_type = '';

    /**
     * Holds the content of the event to listen to.
     *
     * This is a second 'qualifier'. Let's say we do not get something nice
     * like 'user_joined_course_as_member' as $event_type but something opaque
     * like 'course_even', then this second qualifier allows to further precise
     * the needed event. This is necessary to minimize workload for the workflow
     * controllers event handler. Premature optimization you yell? Damn right!
     */
    private string $event_content = '';

    /**
     * Holding the subject type of the event to be listened for.
     *
     * After knowing the 'what', we want to know the 'who'. Using a subject type,
     * 'who' is not limited to actual people but can be anything.
     */
    private string $event_subject_type = '';

    /**
     * This is the actual identifier of the 'who'. If subject_type is a usr, this
     * is a usr_id. If subject_type is a grp, this is a group_id. (or  group ref id)
     *
     * @var int|string Identifier of the events subject.
     *
     */
    private $event_subject_identifier = 0;

    /**
     * Type of the event context.
     *
     * Now we turn to the 'where' of things, completing the generic definition
     * of the event to listen for. The event context type _may_ be implicit part
     * of the definition (e.g. due to a 'crs_member_joined' event type), but it
     * doesn't have to. Example here is a crs as type.
     */
    private string $event_context_type = '';

    /**
     * Identifier of the events context.
     *
     * This can be a course_ref_id, when the context_type is crs or the like.
     *
     * @var int|string Identifier of the events context.
     */
    private $event_context_identifier = 0;

    /**
     * Holds the start of the listening period.
     * @var int Unix timestamp, start of listening period.
     */
    private int $listening_start = 0;

    /**
     * Holds the end of the listening period.
     * @var int Unix timestamp, end of listening period.
     */
    private int $listening_end = 0;

    /**
     * This holds the database id of the detector, if set, or null.
     *
     * @var int|null Database Id of the detector
     */
    private ?int $db_id = null;

    public bool $was_activated = false;

    /**
     * Sets the event type and content (/qualifier) for the detector. 'WHAT'
     * @param string $event_type
     * @param string $event_content
     */
    public function setEvent(string $event_type, string $event_content) : void
    {
        $this->event_type = $event_type;
        $this->event_content = $event_content;
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
     * Set the event subject type to the detector. 'WHO'
     * @param string  $event_subject_type
     * @param int|string $event_subject_identifier
     */
    public function setEventSubject(string $event_subject_type, $event_subject_identifier) : void
    {
        $this->event_subject_type = $event_subject_type;
        $this->event_subject_identifier = $event_subject_identifier;
    }

    /**
     * Get the event subject set to the detector.
     *
     * @return array{type: string, identifier: string|int}
     */
    public function getEventSubject() : array
    {
        return ['type' => $this->event_subject_type, 'identifier' => $this->event_subject_identifier];
    }

    /**
     * Set the event context to the detector. 'WHERE' / 'ON WHAT' / 'ON WHOM'
     * @param string  $event_context_type
     * @param int|string $event_context_identifier
     */
    public function setEventContext(string $event_context_type, $event_context_identifier) : void
    {
        $this->event_context_type = $event_context_type;
        $this->event_context_identifier = $event_context_identifier;
    }

    /**
     * Get the event context set to the detector.
     *
     * @return array{type: string, identifier: string|int}
     */
    public function getEventContext() : array
    {
        return ['type' => $this->event_context_type, 'identifier' => $this->event_context_identifier];
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
     * @return bool|null
     */
    public function trigger($params) : ?bool
    {
        if (!$this->isListening()) {
            return null;
        }

        if ($this->event_type !== $params[0]) {
            // Wrong event type -> no action here.
            return null;
        }

        if ($this->event_content !== $params[1]) {
            // Wrong event content -> no action here.
            return null;
        }

        if ($this->event_subject_type !== $params[2]) {
            // Wrong event subject type -> no action here.
            return null;
        }

        if ($this->event_subject_identifier !== $params[3] && $this->event_subject_identifier != 0) {
            // Wrong event subject identifier and identifier here not 0 (not *all*) -> no action.
            return null;
        }
        
        if ($this->event_context_type !== $params[4]) {
            // Wrong event context type -> no action.
            return null;
        }

        if ($this->event_context_identifier !== $params[5] && $this->event_context_identifier != 0) {
            // Wrong event context identifier and identifier here not 0 (not *all*) -> no action.
            return null;
        }

        // We're through checks now, let's see if this detector is already satisfied.
        if ($this->getDetectorState() === false) {
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
     * @return bool
     */
    public function isListening() : bool
    {
        // No listening phase = always listening.
        if ($this->listening_start === 0 && $this->listening_end === 0) {
            return true;
        }

        // Listening started?
        if ($this->listening_start <= ilWorkflowUtils::time()) {
            // Listening not ended or infinite?
            if ($this->listening_end === 0 || $this->listening_end >= ilWorkflowUtils::time()) {
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
    public function setListeningTimeframe(int $listening_start, int $listening_end) : void
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
     * @return int
     * @throws ilWorkflowObjectStateException
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
     * Returns the listening timefrage of the detector.
     *
     * @return array{listening_start: int, listening_end: int}
     */
    public function getListeningTimeframe() : array
    {
        return ['listening_start' => $this->listening_start, 'listening_end' => $this->listening_end];
    }

    public function getActivated() : bool
    {
        return $this->was_activated;
    }
}
