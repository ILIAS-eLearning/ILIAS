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

use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class DeliveryMethodObjective extends BuildArtifactObjective
{
    public const ARTIFACT = './src/FileDelivery/artifacts/delivery_method.php';
    public const SETTINGS = 'delivery_method';
    public const XSENDFILE = 'xsendfile';
    public const PHP = 'php';

    public function getArtifactPath(): string
    {
        return self::ARTIFACT;
    }

    public function build(): Setup\Artifact
    {
        // check if mod_xsendfile is loaded
        if ($this->isModXSendFileLoaded()) {
            return new Setup\Artifact\ArrayArtifact([
                self::SETTINGS => self::XSENDFILE
            ]);
        }

        return new Setup\Artifact\ArrayArtifact([
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
            if ($command_exists === null) {
                return false;
            }

            $loaded_modules = array_map(static function ($module) {
                return explode(" ", trim($module))[0] ?? "";
            }, explode("\n", shell_exec("apache2ctl -M")));
        } catch (\Throwable $e) {
            $loaded_modules = [];
        }
        if (in_array('xsendfile_module', $loaded_modules, true)) {
            return true;
        }
        return false;
    }
}
