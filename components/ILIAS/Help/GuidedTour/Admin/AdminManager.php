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

namespace ILIAS\Help\GuidedTour\Admin;

use ILIAS\Help\GuidedTour\InternalDomainService;

class AdminManager
{
    protected \ilObjUser $user;
    protected \ILIAS\Help\GuidedTour\Elements\IdPresentation $id_pres;

    public function __construct(
        protected InternalDomainService $domain
    ) {
        $this->id_pres = $domain->idPresentation();
        $this->user = $domain->user();
    }

    public function areIdentifiersVisible(): bool
    {
        return in_array($this->user->getLogin(), $this->id_pres->getValidIdPresentationUsers());
    }
}
