<?php

declare(strict_types=1);

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

use ILIAS\UI\Component\Input\Field;
use ILIAS\Refinery\Factory as Refinery;

/**
 * An object carrying settings of an Individual Assessment obj
 * beyond the standard information
 */
class ilIndividualAssessmentSettings
{
    protected int $obj_id;
    protected string $title;
    protected string $description;
    protected string $content;
    protected string $record_template;
    protected bool $event_time_place_required;
    protected bool $file_required;

    public function __construct(
        int $obj_id,
        string $title,
        string $description,
        string $content,
        string $record_template,
        bool $event_time_place_required,
        bool $file_required
    ) {
        $this->obj_id = $obj_id;
        $this->title = $title;
        $this->description = $description;
        $this->content = $content;
        $this->record_template = $record_template;
        $this->event_time_place_required = $event_time_place_required;
        $this->file_required = $file_required;
    }

    /**
     * Get the id of corresponding iass-object
     */
    public function getObjId(): int
    {
        return $this->obj_id;
    }

    /**
     * Get the content of this assessment, e.g. corresponding topics...
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the content of this assessment, e.g. corresponding topics...
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the content of this assessment, e.g. corresponding topics...
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get the record template to be used as default record with
     * corresponding object
     */
    public function getRecordTemplate(): string
    {
        return $this->record_template;
    }

    /**
     * Get the value of the checkbox event_time_place_require
     */
    public function isEventTimePlaceRequired(): bool
    {
        return $this->event_time_place_required;
    }

    /**
     * Get the value of the checkbox file_required
     */
    public function isFileRequired(): bool
    {
        return $this->file_required;
    }

    public function toFormInput(
        Field\Factory $input,
        ilLanguage $lng,
        Refinery $refinery
    ): Field\Input {
        return $input->section(
            [
                $input->text($lng->txt("title"))
                    ->withValue($this->getTitle())
                    ->withRequired(true),
                $input->textarea($lng->txt("description"))
                    ->withValue($this->getDescription()),
                $input->textarea($lng->txt("iass_content"), $lng->txt("iass_content_explanation"))
                    ->withValue($this->getContent()),
                $input->textarea($lng->txt("iass_record_template"), $lng->txt("iass_record_template_explanation"))
                    ->withValue($this->getRecordTemplate()),
                $input->checkbox($lng->txt("iass_event_time_place_required"), $lng->txt("iass_event_time_place_required_info"))
                    ->withValue($this->isEventTimePlaceRequired()),
                $input->checkbox($lng->txt("iass_file_required"), $lng->txt("iass_file_required_info"))
                    ->withValue($this->isFileRequired())
            ],
            $lng->txt("settings")
        )->withAdditionalTransformation(
            $refinery->custom()->transformation(function ($value) {
                return new ilIndividualAssessmentSettings(
                    $this->getObjId(),
                    ...$value
                );
            })
        );
    }
}
