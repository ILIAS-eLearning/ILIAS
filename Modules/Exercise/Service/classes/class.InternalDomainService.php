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

namespace ILIAS\Exercise;

use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\DI\Container;

/**
 * Exercise domain service (business logic)
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;
    protected InternalDataService $data;
    protected InternalRepoService $repo;
    protected Assignment\DomainService $assignment_service;

    public function __construct(
        Container $DIC,
        InternalDataService $data,
        InternalRepoService $repo
    ) {
        $this->data = $data;
        $this->repo = $repo;
        $this->assignment_service = new Assignment\DomainService(
            $this,
            $repo
        );
        $this->initDomainServices($DIC);
    }

    public function refinery(): \ILIAS\Refinery\Factory
    {
        global $DIC;
        return $DIC->refinery();
    }

    public function assignment(): Assignment\DomainService
    {
        return $this->assignment_service;
    }
}
