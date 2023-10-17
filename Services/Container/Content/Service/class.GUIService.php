<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Container\Content;

use ILIAS\Containter\Content\ItemRenderer;
use ILIAS\Container\InternalDataService;
use ILIAS\Container\InternalDomainService;
use ILIAS\Container\InternalGUIService;
use ILIAS\Containter\Content\ObjectiveRenderer;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class GUIService
{
    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;
    protected InternalGUIService $gui_service;

    public function __construct(
        InternalDataService $data_service,
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->gui_service = $gui_service;
    }

    public function itemRenderer(
        \ilContainerGUI $container_gui,
        string $view_mode      // TILE/LIST from ilContainerContentGUI
    ): ItemRenderer {
        return new ItemRenderer(
            $this->domain_service,
            $this->gui_service,
            $view_mode,
            $container_gui
        );
    }

    public function objectiveRenderer(
        \ilContainerGUI $container_gui,
        string $view_mode,      // TILE/LIST from ilContainerContentGUI
        \ilContainerRenderer $container_render
    ): ObjectiveRenderer {
        return new ObjectiveRenderer(
            $this->domain_service,
            $this->gui_service,
            $view_mode,
            $container_gui,
            $container_render
        );
    }
}
