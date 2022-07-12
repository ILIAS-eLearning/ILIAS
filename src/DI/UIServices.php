<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    public function factory() : \ILIAS\UI\Factory
    {
        return $this->container["ui.factory"];
    }

    /**
     * Get a renderer for UI components.
     */
    public function renderer() : \ILIAS\UI\Renderer
    {
        return $this->container["ui.renderer"];
    }

    /**
     * Get the ILIAS main template.
     *
     * @return \ilGlobalTemplateInterface
     */
    public function mainTemplate() : \ilGlobalTemplateInterface
    {
        return $this->container["tpl"];
    }
}
