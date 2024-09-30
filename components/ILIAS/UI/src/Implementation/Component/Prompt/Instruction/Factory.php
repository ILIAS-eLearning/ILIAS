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

namespace ILIAS\UI\Implementation\Component\Prompt\Instruction;

use ILIAS\UI\Component\Prompt as I;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\Data\URI;

class Factory implements I\Instruction\Factory
{
    public function show(I\PromptContent $content): Instruction
    {
        return new Instruction($content);
    }

    public function close(): Instruction
    {
        return (new Instruction(null))
            ->withCloseModal(true);
    }

    public function redirect(URI $redirect): Instruction
    {
        return (new Instruction(null))
            ->withRedirect($redirect);
    }

}
