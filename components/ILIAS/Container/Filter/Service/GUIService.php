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

namespace ILIAS\Container\Filter;

use ILIAS\Container\InternalDataService;
use ILIAS\Container\InternalDomainService;
use ILIAS\Container\InternalGUIService;

class GUIService
{
    protected InternalGUIService $gui;
    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalDataService $data_service,
        InternalDomainService $domain_service,
        InternalGUIService $gui
    ) {
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->gui = $gui;
    }

    public function containerFilterTableBuilder(
        \ilContainerFilterService $container_filter_service,
        int $ref_id,
        object $parent_gui,
        string $parent_cmd
    ): ContainerFilterTableBuilder {
        return new ContainerFilterTableBuilder(
            $this->domain_service,
            $this->gui,
            $container_filter_service,
            $ref_id,
            $parent_gui,
            $parent_cmd
        );
    }
}
