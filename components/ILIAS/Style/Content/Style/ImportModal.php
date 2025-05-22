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

namespace ILIAS\Style\Content\Style;

use ILIAS\Style\Content\GUIService;
use ILIAS\Style\Content\InternalGUIService;
use ILIAS\Style\Content\InternalDomainService;

class ImportModal
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui
    ) {
    }

    private function getImportModal(): RoundTrip
    {
        $f = $this->gui->ui()->factory();
        $lng = $this->domain->lng();
        return $f->modal()->roundtrip(
            $lng->txt('import'),
            [],
            $this->buildImportFormInputs(),
            $this->ctrl->getFormAction($this, 'routeImportCmd')
        )->withSubmitLabel($this->lng->txt('import'));
    }

}
