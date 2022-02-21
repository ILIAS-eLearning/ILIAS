<?php declare(strict_types=1);

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Input\Field;
use ILIAS\Refinery\Factory as Refinery;

class ilStudyProgrammeTypeSettings
{
    protected int $type_id;

    public function __construct(int $type_id)
    {
        $this->type_id = $type_id;
    }

    public function getTypeId() : int
    {
        return $this->type_id;
    }

    public function withTypeId(int $type_id) : ilStudyProgrammeTypeSettings
    {
        $clone = clone $this;
        $clone->type_id = $type_id;
        return $clone;
    }

    public function toFormInput(
        Field\Factory $input,
        ilLanguage $lng,
        Refinery $refinery,
        array $sp_types
    ) : Field\Input {
        $select = $input
            ->select($lng->txt('type'), $sp_types, $lng->txt('prg_type_byline'))
            ->withValue($this->getTypeId() == 0 ? "" : $this->getTypeId())
            ->withAdditionalTransformation($refinery->custom()->transformation(function ($v) {
                if ($v == "") {
                    return 0;
                }
                return $v;
            }))
        ;

        return $input->section(
            [
                'type' => $select
            ],
            $lng->txt('prg_type')
        )
        ->withAdditionalTransformation($refinery->custom()->transformation(function ($vals) {
            return new ilStudyProgrammeTypeSettings((int) $vals['type']);
        }));
    }
}
