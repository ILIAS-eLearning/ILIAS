<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

/**
 * Provides fluid interface to RBAC services.
 */
class UIServices
{
    /**
     * @var	Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get the factory that crafts UI components.
     *
     * @return \ILIAS\UI\Factory
     */
    public function factory()
    {
        return $this->container["ui.factory"];
    }

    /**
     * Get a renderer for UI components.
     *
     * @return \ILIAS\UI\Renderer
     */
    public function renderer()
    {
        return $this->container["ui.renderer"];
    }

    /**
     * Get the ILIAS main template.
     *
     * @return	\ilTemplate
     */
    public function mainTemplate()
    {
        return $this->container["tpl"];
    }
}
