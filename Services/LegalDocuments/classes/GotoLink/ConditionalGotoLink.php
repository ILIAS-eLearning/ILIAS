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

namespace ILIAS\LegalDocuments\GotoLink;

use Closure;
use ILIAS\LegalDocuments\GotoLink;
use ILIAS\LegalDocuments\Value\Target;

final class ConditionalGotoLink implements GotoLink
{
    /**
     * @param Closure(): Target $select_target
     */
    public function __construct(private readonly string $name, private readonly Closure $select_target)
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function target(): Target
    {
        return ($this->select_target)();
    }

}
