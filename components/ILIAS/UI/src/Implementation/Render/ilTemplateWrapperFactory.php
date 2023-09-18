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

namespace ILIAS\UI\Implementation\Render;

use ilGlobalTemplateInterface;
use ilTemplate;

/**
 * Factory for wrapped ilTemplates.
 */
class ilTemplateWrapperFactory implements TemplateFactory
{
    protected ilGlobalTemplateInterface $global_tpl;

    public function __construct(ilGlobalTemplateInterface $global_tpl)
    {
        $this->global_tpl = $global_tpl;
    }

    /**
     * @inheritdocs
     */
    public function getTemplate(string $path, bool $purge_unfilled_vars, bool $purge_unused_blocks): Template
    {
        $tpl = new ilTemplate($path, $purge_unfilled_vars, $purge_unused_blocks);
        return new ilTemplateWrapper($this->global_tpl, $tpl);
    }
}
