<?php declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;
use ILIAS\Setup;

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
 * Class ilGSBootLoaderBuilder
 * @package ILIAS\GlobalScreen\BootLoader
 */
class ilGlobalScreenBuildProviderMapObjective extends Setup\Artifact\BuildArtifactObjective
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
        
        $finder = new Setup\ImplementationOfInterfaceFinder();
        foreach ($i as $interface) {
            $class_names[$interface] = iterator_to_array(
                $finder->getMatchingClassNames($interface)
            );
        }
        
        return new Setup\Artifact\ArrayArtifact($class_names);
    }
}
