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

namespace ILIAS\DI;

/**
 * Provides fluid interface to RBAC services.
 */
class UIServices
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get the factory that crafts UI components.
     */
    public function factory(): \ILIAS\UI\Factory
    {
        return $this->container["ui.factory"];
    }

    /**
     * Get a renderer for UI components.
     */
    public function renderer(): \ILIAS\UI\Renderer
    {
        return $this->container["ui.renderer"];
    }

    /**
     * Get the ILIAS main template.
     *
     * @return \ilGlobalTemplateInterface
     */
    public function mainTemplate(): \ilGlobalTemplateInterface
    {
        return $this->container["tpl"];
    }
}
