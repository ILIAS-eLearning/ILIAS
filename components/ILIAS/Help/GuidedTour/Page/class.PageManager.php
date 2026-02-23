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

namespace ILIAS\Help\GuidedTour\Page;

use ILIAS\Help\GuidedTour\InternalDomainService;

class PageManager
{
    public function __construct(
        protected InternalDomainService $domain
    ) {
    }

    public function printPage(int $step_id): void
    {
        // get page object
        $page_gui = new \ilGuidedTourPageGUI($step_id);
        $page_gui->setPresentationTitle("");
        $page_gui->setTemplateOutput(false);
        $page_gui->setHeader("");
        $page_gui->showPageFullscreen();
    }
}
