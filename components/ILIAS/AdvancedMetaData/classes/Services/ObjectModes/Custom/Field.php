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

declare(strict_types=1);

namespace ILIAS\AdvancedMetaData\Services\ObjectModes\Custom;

class Field implements FieldInterface
{
    protected string $title;
    protected string $string_value;
    protected string $html_value;

    public function __construct(
        string $title,
        string $string_value,
        string $html_value
    ) {
        $this->title = $title;
        $this->string_value = $string_value;
        $this->html_value = $html_value;
    }

    public function presentableTitle(): string
    {
        return $this->title;
    }

    public function valueAsPresentableString(): string
    {
        return $this->string_value;
    }

    public function valueAsHTML(): string
    {
        return $this->html_value;
    }
}
