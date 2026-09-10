<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Style\Content;

use ILIAS\Style\Content\Access\StyleAccessManager;
use ILIAS\Style\Content\Template\TemplateTableBuilder;

class TemplateUIFactory
{
    public function __construct(
        protected InternalDomainService $domain_service
    ) {
    }

    public function templateTableBuilder(
        \ilObjStyleSheet $style_obj,
        string $temp_type,
        StyleAccessManager $access_manager,
        object $parent_gui,
        string $parent_cmd
    ): TemplateTableBuilder {
        return new TemplateTableBuilder(
            $this->domain_service,
            $style_obj,
            $temp_type,
            $access_manager,
            $parent_gui,
            $parent_cmd
        );
    }
}
