<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\UI\Component\Input\Field;
use ILIAS\Refinery\Factory as Refinery;

class ilIndividualAssessmentInfoSettings
{
    protected int $obj_id;
    protected ?string $contact;
    protected ?string $responsibility;
    protected ?string $phone;
    protected ?string $mails;
    protected ?string $consultation_hours;

    public function __construct(
        int $obj_id,
        ?string $contact = null,
        ?string $responsibility = null,
        ?string $phone = null,
        ?string $mails = null,
        ?string $consultation_hours = null
    ) {
        $this->obj_id = $obj_id;
        $this->contact = $contact;
        $this->responsibility = $responsibility;
        $this->phone = $phone;
        $this->mails = $mails;
        $this->consultation_hours = $consultation_hours;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getContact() : ?string
    {
        return $this->contact;
    }

    public function getResponsibility() : ?string
    {
        return $this->responsibility;
    }

    public function getPhone() : ?string
    {
        return $this->phone;
    }

    public function getMails() : ?string
    {
        return $this->mails;
    }

    public function getConsultationHours() : ?string
    {
        return $this->consultation_hours;
    }

    public function toFormInput(
        Field\Factory $input,
        ilLanguage $lng,
        Refinery $refinery
    ) : Field\Input {
        return $input->section(
            [
                $input->text($lng->txt("iass_contact"))
                    ->withValue((string) $this->getContact())
                    ->withRequired(true),
                $input->text($lng->txt("iass_responsibility"))
                    ->withValue((string) $this->getResponsibility()),
                $input->text($lng->txt("iass_phone"))
                    ->withValue((string) $this->getPhone()),
                $input->textarea($lng->txt("iass_mails"), $lng->txt("iass_info_emails_expl"))
                    ->withValue((string) $this->getMails()),
                $input->textarea($lng->txt("iass_consultation_hours"))
                    ->withValue((string) $this->getConsultationHours())
            ],
            $lng->txt("settings")
        )->withAdditionalTransformation(
            $refinery->custom()->transformation(function ($value) {
                return new ilIndividualAssessmentInfoSettings(
                    $this->getObjId(),
                    ...$value
                );
            })
        );
    }
}
