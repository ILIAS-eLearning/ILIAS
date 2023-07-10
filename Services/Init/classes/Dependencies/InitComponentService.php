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

/**
 * Responsible for loading the Component Service into the dependency injection container of ILIAS
 */
class InitComponentService
{
    public function init(\ILIAS\DI\Container $c): void
    {
        $int = $this->initInternal($c);

        $c["component.repository"] = fn ($c): \ilComponentRepository => $int["db_write"];

        $c["component.factory"] = fn ($c): \ilComponentFactory => new ilComponentFactoryImplementation(
            $int["db_write"],
            $c["ilDB"]
        );
    }

    public function initInternal(\ILIAS\DI\Container $c): \Pimple\Container
    {
        $int = new \Pimple\Container();
        $data_factory = new \ILIAS\Data\Factory();

        $int["plugin_state_db"] = fn ($int): \ilPluginStateDB => new ilPluginStateDBOverIlDBInterface(
            $data_factory,
            $c["ilDB"]
        );

        $int["db_write"] = fn ($int): \ilComponentRepositoryWrite => new ilArtifactComponentRepository(
            $data_factory,
            $int["plugin_state_db"],
            $c["ilias.version"]
        );

        return $int;
    }
}
