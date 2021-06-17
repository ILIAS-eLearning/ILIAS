<?php

/* Copyright (c) 2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 */
class ilComponentDefinitionReader
{
    /**
     * @var ilComponentDefinitionProcessor[]
     */
    protected array $processors;

    public function __construct(
        ilComponentDefinitionProcessor ...$processor
    ) {
        $this->processor = $processor;
    }

    /**
     * This methods is supposed to purge existing data in the registered
     * processor.
     */
    public function purge() : void
    {
        foreach ($this->processor as $p) {
            $p->purge();
        }
    }

    /**
     * Get paths to all component.xmls in the core.
     *
     * TODO: Currently this wraps the existing methods `ilModule::getAvailableCoreModules`
     * and `ilService::getAvailableCoreServices`, we will want to replace this by some
     * artifact some day.
     *
     * @return string[]
     */
    protected function getComponents() : array
    {
        $modules_dir = __DIR__ . "/../../../Modules";
        $services_dir = __DIR__ . "/../../../Services";
        return array_merge(
            array_map(
                fn ($path) => realpath($modules_dir . "/" . $path["subdir"] . "/module.xml"),
                ilModule::getAvailableCoreModules()
            ),
            array_map(
                fn ($path) => realpath($services_dir . "/" . $path["subdir"] . "/service.xml"),
                ilService::getAvailableCoreServices()
            )
        );
    }
}
