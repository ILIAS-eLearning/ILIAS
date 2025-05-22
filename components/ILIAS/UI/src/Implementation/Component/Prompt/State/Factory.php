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

use ILIAS\UI\Component\Prompt as I;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\Data\URI;

class Factory implements I\State\Factory
{
    public function show(I\IsPromptContent $content): State
    {
        return new State($content);
    }

    public function close(): State
    {
        return (new State(null))
            ->withCloseModal(true);
    }

    public function redirect(URI $redirect): State
    {
        return (new State(null))
            ->withRedirect($redirect);
    }

}
