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

namespace ILIAS\UI\Implementation\Component\Prompt\State;

use ILIAS\Data\URI;
use ILIAS\UI\Component as C;

class Factory implements C\Prompt\State\Factory
{
    public function show(C\Prompt\IsPromptContent $primary_content, C\Prompt\IsPromptContent ...$secondary_content): State
    {
        return new State(Instruction::SHOW, null, $primary_content, $secondary_content);
    }

    public function close(): State
    {
        return new State(Instruction::CLOSE, null, null);
    }

    public function redirect(URI $redirect): State
    {
        return new State(Instruction::REDIRECT, $redirect, null);
    }
}
