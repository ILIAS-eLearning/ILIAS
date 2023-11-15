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

namespace ILIAS\Glossary;

use ILIAS\DI\Container;

class InternalService
{
    protected Container $DIC;
    protected array $instance = [];

    public function __construct(Container $DIC)
    {
        $this->DIC = $DIC;
    }

    public function data(): InternalDataService
    {
        return $this->instance["data"] ??
            $this->instance["data"] = new InternalDataService();
    }

    public function repo(): InternalRepoService
    {
        return $this->instance["repo"] ??
            $this->instance["repo"] = new InternalRepoService(
                $this->data(),
                $this->DIC->database()
            );
    }

    public function domain(): InternalDomainService
    {
        return $this->instance["domain"] ??
            $this->instance["domain"] = new InternalDomainService(
                $this->DIC,
                $this->repo(),
                $this->data()
            );
    }

    public function gui(): InternalGUIService
    {
        return $this->instance["gui"] ??
            $this->instance["gui"] = new InternalGUIService(
                $this->DIC,
                $this->data(),
                $this->domain()
            );

    }
}
