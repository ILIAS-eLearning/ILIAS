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

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as C;

class Boolean extends Column implements C\Boolean
{
    protected string $true_option;
    protected string $false_option;

    public function __construct(
        string $title,
        string $true_option,
        string $false_option
    ) {
        parent::__construct($title);
        $this->true_option = $true_option;
        $this->false_option = $false_option;
    }

    public function format($value): string
    {
        $this->checkBoolArg('value', $value);
        return $value ? $this->true_option : $this->false_option;
    }
}
