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

namespace ILIAS\LegalDocuments\PageFragment;

use ILIAS\LegalDocuments\PageFragment;
use ilGlobalTemplateInterface;
use ILIAS\UI\Renderer;

class ShowOnScreenMessage implements PageFragment
{
    public function __construct(
        private readonly PageFragment $inner,
        private readonly string $type,
        private readonly string $txt,
        private readonly bool $keep
    ) {
    }

    public function render(ilGlobalTemplateInterface $main_template, Renderer $renderer): string
    {
        $main_template->setOnScreenMessage($this->type, $this->txt, $this->keep);
        return $this->inner->render($main_template, $renderer);
    }
}
