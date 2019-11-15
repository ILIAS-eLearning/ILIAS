<?php

use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;
use ILIAS\Setup;

/**
 * Class ilGSBootLoaderBuilder
 *
 * @package ILIAS\GlobalScreen\BootLoader
 */
class ilGlobalScreenBuildProviderMapObjective extends Setup\BuildArtifactObjective
{

    public function getArtifactPath() : string
    {
        return "Services/GlobalScreen/artifacts/global_screen_providers.php";
    }


    public function build() : Setup\Artifact
    {
        $class_names = [];
        $i = [
            StaticMainMenuProvider::class,
            StaticMetaBarProvider::class,
            DynamicToolProvider::class,
            ModificationProvider::class,
            NotificationProvider::class,
        ];

        foreach ($i as $interface) {
            $i = new Setup\ImplementationOfInterfaceFinder($interface);
            $class_names[$interface] = iterator_to_array($i->getMatchingClassNames());
        }

        return new Setup\ArrayArtifact($class_names);
    }
}
