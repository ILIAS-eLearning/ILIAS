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

use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup\Artifact;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;
use ILIAS\GlobalScreen\Scope\Toast\Provider\ToastProvider;
use ILIAS\GlobalScreen\Scope\Footer\Provider\StaticFooterProvider;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilGlobalScreenBuildProviderMapObjective extends BuildArtifactObjective
{
    public function getArtifactName(): string
    {
        return "global_screen_providers";
    }


    public function build(): Artifact
    {
        $class_names = [];
        $i = [
            StaticMainMenuProvider::class,
            StaticMetaBarProvider::class,
            StaticFooterProvider::class,
            DynamicToolProvider::class,
            ModificationProvider::class,
            NotificationProvider::class,
            ToastProvider::class
        ];

        $finder = new ImplementationOfInterfaceFinder();
        foreach ($i as $interface) {
            $class_names[$interface] = iterator_to_array(
                $finder->getMatchingClassNames($interface)
            );
        }

        return new ArrayArtifact($class_names);
    }
}
