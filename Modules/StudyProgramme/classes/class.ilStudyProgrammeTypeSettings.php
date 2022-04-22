<?php declare(strict_types=1);

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
            ->withValue($this->getTypeId() === 0 ? "" : $this->getTypeId())
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
