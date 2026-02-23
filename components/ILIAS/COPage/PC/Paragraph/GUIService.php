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

namespace ILIAS\COPage\PC\Paragraph;

use ILIAS\COPage\InternalGUIService;
use ILIAS\COPage\InternalDomainService;

class GUIService
{
    public function __construct(
        protected InternalDomainService $domain_service,
        protected InternalGUIService $gui_service
    ) {
    }

    public function menu(): MenuGUI
    {
        global $DIC;
        $style_service = $DIC->contentStyle()->internal();
        return new MenuGUI(
            $this->domain_service,
            $this->gui_service,
            $style_service
        );
    }
}
