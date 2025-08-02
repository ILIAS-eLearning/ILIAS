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

namespace ILIAS\User\Settings;

use ILIAS\Setup\Artifact;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact\BuildArtifactObjective;

class CollectSettingsObjective extends BuildArtifactObjective
{
    public function __construct(
        private readonly array $contributions
    ) {
    }

    public function getArtifactName(): string
    {
        return 'user_settings';
    }

    public function build(): Artifact
    {
        return new ArrayArtifact(
            array_reduce(
                $this->contributions,
                static fn(array $c, UserSettings $settings): array => array_merge($c, $settings->getSettingConfigurations()),
                []
            )
        );
    }
}
