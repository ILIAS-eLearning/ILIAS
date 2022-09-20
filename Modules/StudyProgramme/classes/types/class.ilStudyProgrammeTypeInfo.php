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

class ilStudyProgrammeTypeInfo
{
    protected ?string $title;
    protected ?string $description;
    protected ?string $lng_code;

    public function __construct(
        string $title = null,
        string $description = null,
        string $lng_code = null
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->lng_code = $lng_code;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function withTitle(string $title): ilStudyProgrammeTypeInfo
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function withDescription(string $description): ilStudyProgrammeTypeInfo
    {
        $clone = clone $this;
        $clone->description = $description;
        return $clone;
    }

    public function getLanguageCode(): ?string
    {
        return $this->lng_code;
    }

    public function withLanguageCode(string $lng_code): ilStudyProgrammeTypeInfo
    {
        $clone = clone $this;
        $clone->lng_code = $lng_code;
        return $clone;
    }

    public function toFormInput(
        Field\Factory $input,
        ilLanguage $lng,
        Refinery $refinery
    ): Field\Input {
        $title = $input
            ->text($lng->txt('title'), '')
            ->withValue($this->getTitle() ?? "")
            ->withRequired(true)
        ;

        $description = $input
            ->textarea($lng->txt('description'), '')
            ->withValue($this->getDescription() ?? "")
            ->withRequired(true)
        ;

        $lng_code = $this->getLanguageCode() ?? "";

        return $input->section(
            [
                'title' => $title,
                'description' => $description
            ],
            $lng->txt("meta_l_$lng_code")
        )
        ->withAdditionalTransformation($refinery->custom()->transformation(function ($vals) use ($lng_code) {
            return new ilStudyProgrammeTypeInfo(
                $vals['title'],
                $vals['description'],
                $lng_code
            );
        }));
    }
}
