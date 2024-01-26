<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;

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
     * @var ?string
     */
    protected $file;

    /**
     * @var bool
     */
    protected $is_file_visible;

    /**
     * @var int
     */
    protected $learning_progress;

    /**
     * @var string
     */
    protected $place;

    /**
     * @var DateTimeImmutable|null
     */
    protected $event_time;

    /**
     * @var bool
     */
    protected $notify;

    /**
     * @var bool
     */
    protected $finalized;

    public function __construct(
        string $name,
        string $record,
        string $internal_note,
        ?string $file,
        bool $is_file_visible,
        int $learning_progress,
        string $place,
        ?DateTimeImmutable $event_time,
        bool $notify,
        bool $finalized = false
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
        $this->finalized = $finalized;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getRecord() : string
    {
        return $this->record;
    }

    public function getInternalNote() : string
    {
        return $this->internal_note;
    }

    public function getFile() : ?string
    {
        return $this->file;
    }

    public function hasFile() : bool
    {
        return !empty($this->file);
    }

    public function isFileVisible() : bool
    {
        return $this->is_file_visible;
    }

    public function getLearningProgress() : int
    {
        return $this->learning_progress;
    }

    public function getPlace() : string
    {
        return $this->place;
    }

    public function getEventTime() : ?DateTimeImmutable
    {
        return $this->event_time;
    }

    public function isNotify() : bool
    {
        return $this->notify;
    }

    public function isFinalized() : bool
    {
        return $this->finalized;
    }

    public function withFinalized(bool $finalize) : ilIndividualAssessmentUserGrading
    {
        $clone = clone $this;
        $clone->finalized = $finalize;
        return $clone;
    }

    public function withFile(?string $file) : ilIndividualAssessmentUserGrading
    {
        $clone = clone $this;
        $clone->file = $file;
        return $clone;
    }

    public function toFormInput(
        Field\Factory $input,
        DataFactory $data_factory,
        \ilLanguage $lng,
        Refinery $refinery,
        array $grading_options,
        bool $may_be_edited = true,
        bool $place_required = false,
        bool $amend = false,
        ilIndividualAssessmentMemberGUI $file_handler
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

        $file = $input
            ->file($file_handler, $lng->txt('iass_upload_file'), $lng->txt('iass_file_dropzone'))
            ->withValue($this->hasFile() ? [$this->getFile()] : null)
        ;

        $file_visible = $input
            ->checkbox($lng->txt('iass_file_visible_examinee'))
            ->withValue($this->isFileVisible())
            ->withDisabled(!$may_be_edited)
        ;

        $learning_progress = $input
            ->select($lng->txt('grading'), $grading_options)
            ->withValue($this->getLearningProgress() ? $this->getLearningProgress() : ilIndividualAssessmentMembers::LP_IN_PROGRESS)
            ->withDisabled(!$may_be_edited)
            ->withRequired(true)
        ;

        $place = $input
            ->text($lng->txt('iass_place'))
            ->withValue($this->getPlace())
            ->withRequired($place_required)
            ->withDisabled(!$may_be_edited)
        ;

        $event_time = $input
            ->dateTime($lng->txt('iass_event_time'))
            ->withRequired($place_required)
            ->withDisabled(!$may_be_edited)
        ;

        if (!is_null($this->getEventTime())) {
            $format = $data_factory->dateFormat()->standard()->toString();
            $event_time = $event_time->withValue($this->getEventTime()->format($format));
        }

        $notify = $input
            ->checkbox($lng->txt('iass_notify'), $lng->txt('iass_notify_explanation'))
            ->withValue($this->isNotify())
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
            $finalized = $input
                ->checkbox($lng->txt('iass_finalize'), $lng->txt('iass_finalize_info'))
                ->withValue($this->isFinalized())
                ->withDisabled(!$may_be_edited)
            ;

            $fields['finalized'] = $finalized;
        }

        return $input->section(
            $fields,
            $lng->txt('iass_edit_record')
        )->withAdditionalTransformation(
            $refinery->custom()->transformation(function ($values) use ($amend) {
                $finalized = $this->isFinalized();
                if (!$amend) {
                    $finalized = $values['finalized'];
                }

                $file = $this->getFile();
                if (
                    isset($values['file'][0]) &&
                    trim($values['file'][0]) != ''
                ) {
                    $file = $values['file'][0];
                }

                return new ilIndividualAssessmentUserGrading(
                    $values['name'],
                    $values['record'],
                    $values['internal_note'],
                    $file,
                    $values['file_visible'],
                    (int) $values['learning_progress'],
                    $values['place'],
                    $values['event_time'],
                    $values['notify'],
                    $finalized
                );
            })
        );
    }
}
