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

namespace ILIAS\FileDelivery\Setup;

use ILIAS\Setup\Artifact;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Artifact\BuildArtifactObjective;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class DeliveryMethodObjective extends BuildArtifactObjective
{
    public const SETTINGS = 'delivery_method';
    public const XSENDFILE = 'xsendfile';
    public const XACCEL = 'xaccel';
    public const PHP = 'php';

    public function getArtifactName(): string
    {
        return "delivery_method";
    }



    public function build(): Artifact
    {
        // check if mod_xsendfile is loaded
        if ($this->isModXSendFileLoaded()) {
            return new ArrayArtifact([
                self::SETTINGS => self::XSENDFILE
            ]);
        }

        return new ArrayArtifact([
            self::SETTINGS => self::PHP
        ]);
    }

    private function isModXSendFileLoaded(): bool
    {
        if (function_exists('apache_get_modules') && in_array('mod_xsendfile', apache_get_modules(), true)) {
            return true;
        }

        try {
            $command_exists = shell_exec("which apache2ctl");
            if ($command_exists === null || empty($command_exists)) {
                return false;
            }

            $loaded_modules = array_map(static fn($module): string => explode(" ", trim((string) $module))[0] ?? "", explode("\n", shell_exec("apache2ctl -M 2>/dev/null") ?? ''));
        } catch (\Throwable) {
            $loaded_modules = [];
        }
        return in_array('xsendfile_module', $loaded_modules, true);
    }

    #[\Override]
    public function isApplicable(Environment $environment): bool
    {
        return !file_exists(BuildArtifactObjective::PATH());
    }

}
