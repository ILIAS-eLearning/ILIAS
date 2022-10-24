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

namespace ILIAS\Container;

use ILIAS\DI;

/**
 * Repository internal service
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalService
{
    protected InternalDataService $data;
    protected InternalRepoService $repo;
    protected InternalDomainService $domain;
    protected InternalGUIService $gui;

    public function __construct(DI\Container $DIC)
    {
        $this->data = new InternalDataService();

        $this->repo = new InternalRepoService(
            $this->data(),
            $DIC->database()
        );
        $this->domain = new InternalDomainService(
            $DIC,
            $this->repo,
            $this->data
        );
        $this->gui = new InternalGUIService(
            $DIC,
            $this->data,
            $this->domain
        );
    }

    public function data(): InternalDataService
    {
        return $this->data;
    }

    public function repo(): InternalRepoService
    {
        return $this->repo;
    }

    public function domain(): InternalDomainService
    {
        return $this->domain;
    }

    public function gui(): InternalGUIService
    {
        return $this->gui;
    }
}
