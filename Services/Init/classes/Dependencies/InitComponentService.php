<?php
/**
 * Responsible for loading the Component Service into the dependency injection container of ILIAS
 */
class InitComponentService
{
    public function init(\ILIAS\DI\Container $c)
    {
        $int = $this->initInternal($c);

        $c["component.db"] = fn ($c) : \ilComponentDataDB =>
            $int["db_write"];

        $c["component.factory"] = fn ($c) : \ilComponentFactory =>
            new ilComponentFactoryImplementation(
                $int["db_write"],
                $c["ilDB"]
            );
    }

    public function initInternal(\ILIAS\DI\Container $c) : \Pimple\Container
    {
        $int = new \Pimple\Container;
        $data_factory = new \ILIAS\Data\Factory();

        $int["plugin_state_db"] = fn ($int) : \ilPluginStateDB =>
            new ilPluginStateDBOverIlDBInterface(
                $data_factory,
                $c["ilDB"]
            );

        $int["db_write"] = fn ($int) : \ilComponentDataDBWrite =>
            new ilArtifactComponentDataDB(
                $data_factory,
                $int["plugin_state_db"],
                $c["ilias.version"]
            );

        return $int;
    }
}
