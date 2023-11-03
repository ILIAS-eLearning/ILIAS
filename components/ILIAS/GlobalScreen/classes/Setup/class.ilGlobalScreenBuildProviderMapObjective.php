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

declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;
use ILIAS\Setup;
use ILIAS\GlobalScreen\Scope\Toast\Provider\ToastProvider;

/**
 * Class ilGSBootLoaderBuilder
 * @package ILIAS\GlobalScreen\BootLoader
 */
class ilGlobalScreenBuildProviderMapObjective extends Setup\Artifact\BuildArtifactObjective
{
    public function getArtifactPath(): string
    {
        return "Services/GlobalScreen/artifacts/global_screen_providers.php";
    }

    public function build(): Setup\Artifact
    {
        $class_names = [];
        $i = [
            StaticMainMenuProvider::class,
            StaticMetaBarProvider::class,
            DynamicToolProvider::class,
            ModificationProvider::class,
            NotificationProvider::class,
            ToastProvider::class
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
