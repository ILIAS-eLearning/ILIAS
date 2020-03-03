<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Refinery\Factory as Refinery;

class ilStudyProgrammeAdditionalSettings
{
    /**
     * @var bool
     */
    protected $access_by_orgu;

    public function __construct(bool $access_by_orgu = true)
    {
        $this->access_by_orgu = $access_by_orgu;
    }

    public function getAccessByOrgu() : bool
    {
        return $this->access_by_orgu;
    }

    public function withAccessByOrgu(bool $access_by_orgu) : ilStudyProgrammeAdditionalSettings
    {
        $clone = clone $this;
        $clone->access_by_orgu = $access_by_orgu;
        return $clone;
    }

    public function toFormInput(
        Field\Factory $input,
        \ilLanguage $lng,
        Refinery $refinery
    ) : Field\Input {
        $checkbox = $input
            ->checkbox($lng->txt('prg_access_by_orgu'), $lng->txt('prg_access_by_orgu_byline'))
            ->withValue($this->getAccessByOrgu())
        ;

        return $input->section(
            [
                'access_ctr_by_orgu_position' => $checkbox
            ],
            $lng->txt('prg_additional_settings')
        )
        ->withAdditionalTransformation($refinery->custom()->transformation(function ($vals) {
            return new ilStudyProgrammeAdditionalSettings(
                (bool) $vals['access_ctr_by_orgu_position']
            );
        }));
    }
}
