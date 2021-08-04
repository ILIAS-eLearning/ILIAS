<?php
/**
 * Responsible for loading the Component Service into the dependency injection container of ILIAS
 */
class InitComponentService
{
    public function init(\ILIAS\DI\Container $c)
    {
        $data_factory = new \ILIAS\Data\Factory();

        $c["component.db"] = fn ($c) : \ilComponentDataDB =>
            new ilArtifactComponentDataDB(
                $data_factory,
                $c["component.plugin_state_db"],
                $c["ilias.version"]
            );

        $c["component.plugin_state_db"] = fn ($c) : \ilPluginStateDB =>
            new ilPluginStateDBOverIlDBInterface(
                $data_factory,
                $c["ilDB"]
            );
    }
}
