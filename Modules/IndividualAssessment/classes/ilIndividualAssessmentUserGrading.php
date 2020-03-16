<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Refinery\Factory as Refinery;

class ilIndividualAssessmentUserGrading
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $record;

    /**
     * @var string
     */
    protected $internal_note;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var bool
     */
    protected $is_file_visible;

    /**
     * @var string
     */
    protected $learning_progress;

    /**
     * @var string
     */
    protected $place;

    /**
     * @var DateTime
     */
    protected $event_time;

    /**
     * @var bool
     */
    protected $notify;

    /**
     * @var bool
     */
    protected $finalize;

    public function __construct(
        string $name,
        string $record,
        string $internal_note,
        string $file,
        bool $is_file_visible,
        string $learning_progress,
        string $place,
        DateTimeImmutable $event_time,
        bool $notify,
        bool $finalize = false
    ) {
        $this->name = $name;
        $this->record = $record;
        $this->internal_note = $internal_note;
        $this->file = $file;
        $this->is_file_visible = $is_file_visible;
        $this->learning_progress = $learning_progress;
        $this->place = $place;
        $this->event_time = $event_time;
        $this->notify = $notify;
        $this->finalize = $finalize;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    public function withName(string $name) : ilIndividualAssessmentUserGrading
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    /**
     * @return string
     */
    public function getRecord() : string
    {
        return $this->record;
    }

    public function withRecord(string $record) : ilIndividualAssessmentUserGrading
    {
        $clone = clone $this;
        $clone->name = $record;
        return $clone;
    }

    /**
     * @return string
     */
    public function getInternalNote() : string
    {
        return $this->internal_note;
    }

    public function withInternalNote(string $internal_note) : ilIndividualAssessmentUserGrading
    {
        $clone = clone $this;
        $clone->name = $internal_note;
        return $clone;
    }

    /**
     * @return string
     */
    public function getFile() : string
    {
        return $this->file;
    }

    public function withFile(string $file) : ilIndividualAssessmentUserGrading
    {
        $clone = clone $this;
        $clone->file = $file;
        return $clone;
    }

    /**
     * @return bool
     */
    public function isFileVisible() : bool
    {
        return $this->is_file_visible;
    }

    public function withIsFileVisible(bool $is_file_visible) : ilIndividualAssessmentUserGrading
    {
        $clone = clone $this;
        $clone->is_file_visible = $is_file_visible;
        return $clone;
    }

    /**
     * @return string
     */
    public function getLearningProgress() : string
    {
        return $this->learning_progress;
    }

    public function withLearningProgress(string $learning_progress) : ilIndividualAssessmentUserGrading
    {
        $clone = clone $this;
        $clone->learning_progress = $learning_progress;
        return $clone;
    }

    /**
     * @return string
     */
    public function getPlace() : string
    {
        return $this->place;
    }

    public function withPlace(string $place) : ilIndividualAssessmentUserGrading
    {
        $clone = clone $this;
        $clone->place = $place;
        return $clone;
    }

    /**
     * @return DateTime
     */
    public function getEventTime() : DateTimeImmutable
    {
        return $this->event_time;
    }

    public function withEventTime(DateTimeImmutable $event_time) : ilIndividualAssessmentUserGrading
    {
        $clone = clone $this;
        $clone->event_time = $event_time;
        return $clone;
    }

    /**
     * @return bool
     */
    public function isNotify() : bool
    {
        return $this->notify;
    }

    public function withNotify(bool $notify) : ilIndividualAssessmentUserGrading
    {
        $clone = clone $this;
        $clone->notify = $notify;
        return $clone;
    }

    public function isFinalize() : bool
    {
        return $this->finalize;
    }

    public function withFinalize(bool $finalize) : ilIndividualAssessmentUserGrading
    {
        $clone = clone $this;
        $clone->finalize = $finalize;
        return $clone;
    }

    public function toFormInput(
        Field\Factory $input,
        \ilLanguage $lng,
        Refinery $refinery,
        array $options,
        bool $may_be_edited = true,
        bool $place_required = false,
        bool $amend = false
    ) : Field\Input {
        $name = $input
            ->text($lng->txt('name'), '')
            ->withDisabled(true)
            ->withValue($this->getName())
        ;

        $record = $input
            ->textarea($lng->txt('iass_record'), $lng->txt('iass_record_info'))
            ->withValue($this->getRecord())
            ->withDisabled(!$may_be_edited)
        ;

        $internal_note = $input
            ->textarea($lng->txt('iass_internal_note'), $lng->txt('iass_internal_note_info'))
            ->withValue($this->getInternalNote())
            ->withDisabled(!$may_be_edited)
        ;

        $handler = ilIndividualAssessmentDIC::dic()['ilIndividualAssessmentMemberGUI'];
        $file = $input
            ->file($handler, $lng->txt('iass_upload_file'), $lng->txt('iass_file_dropzone'))
            ->withValue([$this->getFile()])
        ;

        $file_visible = $input
            ->checkbox($lng->txt('iass_file_visible_examinee'))
            ->withValue($this->isFileVisible())
            ->withDisabled(!$may_be_edited)
        ;

        $learning_progress = $input
            ->select($lng->txt('grading'), $options)
            ->withValue($this->getLearningProgress() ? $this->getLearningProgress() : '')
            ->withDisabled(!$may_be_edited)
        ;

        $place = $input
            ->text($lng->txt('iass_place'))
            ->withValue($this->getPlace())
            ->withRequired($place_required)
            ->withDisabled(!$may_be_edited)
        ;

        $event_time = $input
            ->dateTime($lng->txt('iass_event_time'))
            ->withValue($this->getEventTime()->format('d-m-Y'))
            ->withRequired($place_required)
            ->withDisabled(!$may_be_edited)
        ;

        $notify = $input
            ->checkbox($lng->txt('iass_notify'), $lng->txt('iass_notify_explanation'))
            ->withValue($this->isNotify())
            ->withDisabled(!$may_be_edited)
        ;

        $finalize = $input
            ->checkbox($lng->txt('iass_finalize'), $lng->txt('iass_finalize_info'))
            ->withValue($this->isFinalize())
            ->withDisabled(!$may_be_edited)
        ;

        $fields = [
            'name' => $name,
            'record' => $record,
            'internal_note' => $internal_note,
            'file' => $file,
            'file_visible' => $file_visible,
            'learning_progress' => $learning_progress,
            'place' => $place,
            'event_time' => $event_time,
            'notify' => $notify
        ];

        if (!$amend) {
            $fields['finalize'] = $finalize;
        }

        return $input->section(
            $fields,
            $lng->txt('iass_edit_record')
        )->withAdditionalTransformation(
            $refinery->custom()->transformation(function ($values) use ($amend) {
                $finalize = false;
                if (!$amend) {
                    $finalize = $values['finalize'];
                }

                return new ilIndividualAssessmentUserGrading(
                    $values['name'],
                    $values['record'],
                    $values['internal_note'],
                    $this->getFile(),
                    $values['file_visible'],
                    $values['learning_progress'],
                    $values['place'],
                    $values['event_time'],
                    $values['notify'],
                    $finalize
                );
            })
        );
    }
}
