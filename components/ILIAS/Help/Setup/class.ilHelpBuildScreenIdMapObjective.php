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
use ILIAS\Services\Help\ScreenId\HelpScreenId;
use ILIAS\Services\Help\ScreenId\RecurringHelpScreenId;
use ILIAS\Services\Help\ScreenId\SilentHelpScreenId;

class ilHelpBuildScreenIdMapObjective extends Setup\Artifact\BuildArtifactObjective
{
    public const ARTIFACT = "./Services/Help/artifacts/screen_id_map.php";

    public function getArtifactPath(): string
    {
        return self::ARTIFACT;
    }

    public function getArtifactName(): string
    {
        return "screen_id_map";
    }


    public function build(): Setup\Artifact
    {
        $finder = new Setup\UsageOfAttributeFinder();
        $map = [];
        $get_name = function (string $class_name, string $attribute_name): ?string {
            $reflection = new \ReflectionClass($class_name);
            $attributes = $reflection->getAttributes($attribute_name);
            if (empty($attributes) || !isset($attributes[0])) {
                return null;
            }
            /** @var HelpScreenId $attribute */
            $attribute = $attributes[0]->newInstance();
            return $attribute->getScreenId();
        };

        // Silent
        foreach ($finder->getMatchingClassNames(SilentHelpScreenId::class) as $matching_class_name) {
            $map[$matching_class_name] = null;
        }
        // Recurring
        foreach ($finder->getMatchingClassNames(RecurringHelpScreenId::class) as $matching_class_name) {
            $map[$matching_class_name] = '*' . $get_name($matching_class_name, RecurringHelpScreenId::class);
        }
        // Normal
        foreach ($finder->getMatchingClassNames(HelpScreenId::class) as $matching_class_name) {
            $map[$matching_class_name] = $get_name($matching_class_name, HelpScreenId::class);
        }

        // Check for duplicates
        $check = array_filter(array_diff_assoc($map, array_unique($map)), function ($v): bool {
            return !is_null($v);
        });
        if ($check !== []) {
            throw new Setup\UnachievableException("Duplicate screen ids found: " . implode(', ', $check));
        }

        return new Setup\Artifact\ArrayArtifact($map);
    }
}
