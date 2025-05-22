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

namespace ILIAS\MetaData\Services\DataHelper;

use ILIAS\MetaData\Elements\Data\DataInterface;

class LabelledValue implements LabelledValueInterface
{
    protected string $value;
    protected string $label;

    public function __construct(
        string $value,
        string $label
    ) {
        $this->value = $value;
        $this->label = $label;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function presentableLabel(): string
    {
        return $this->label;
    }
}
