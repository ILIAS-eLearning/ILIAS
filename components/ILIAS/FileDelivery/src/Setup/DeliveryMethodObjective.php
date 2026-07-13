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
use ILIAS\Setup\CLI\IOWrapper;
use ILIAS\Setup\UnachievableException;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class DeliveryMethodObjective extends BuildStaticConfigStoredObjective
{
    public const SETTINGS = 'delivery_method';
    public const SETTINGS_EXTERNAL_DATA_DIR = 'ext_data_dir';
    public const XSENDFILE = 'xsendfile';
    public const XACCEL = 'xaccel';
    public const PHP = 'php';

    private ?string $ext_data_dir = null;
    private ?IOWrapper $io = null;

    public function getArtifactName(): string
    {
        return 'delivery_method';
    }

    public function getPreconditions(Environment $environment): array
    {
        return array_merge(
            parent::getPreconditions($environment),
            [
                new \ilIniFilesLoadedObjective()
            ]
        );
    }

    public function achieve(Environment $environment): Environment
    {
        /** @var \ilIniFile $ini */
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);
        if ($ini instanceof \ilIniFile && $ini->variableExists('clients', 'datadir')) {
            $this->ext_data_dir = $ini->readVariable('clients', 'datadir');
        } else {
            throw new UnachievableException(
                'Could not determine external data directory from ILIAS ini file'
            );
        }

        $io = $environment->getResource(Environment::RESOURCE_ADMIN_INTERACTION);
        if ($io instanceof IOWrapper) {
            $this->io = $io;
        }

        return parent::achieve($environment);
    }

    public function build(): Artifact
    {
        $delivery_method = self::PHP;

        if (file_exists(self::PATH())) {
            $settings = (@include self::PATH()) ?? [];
            $delivery_method = $settings[self::SETTINGS] ?? self::PHP;
        }

        if ($this->isModXSendFileLoaded()) {
            $delivery_method = self::XSENDFILE;
        }

        return new ArrayArtifact(array_filter([
            self::SETTINGS => $delivery_method,
            self::SETTINGS_EXTERNAL_DATA_DIR => $this->ext_data_dir
        ]));
    }

    private function isModXSendFileLoaded(): bool
    {
        if (\function_exists('apache_get_modules') && \in_array('mod_xsendfile', apache_get_modules(), true)) {
            return true;
        }

        try {
            $command_exists = shell_exec('which apache2ctl');
            if (empty($command_exists)) {
                return false;
            }

            $loaded_modules = array_map(
                static fn(string $module): string => explode(' ', trim($module))[0] ?? '',
                explode("\n", shell_exec('apache2ctl -M 2>/dev/null') ?? '')
            );
        } catch (\Throwable $e) {
            $this->io?->error($e->getMessage());
            $this->io?->error($e->getTraceAsString());
            $loaded_modules = [];
        }

        return \in_array('xsendfile_module', $loaded_modules, true);
    }

    #[\Override]
    public function isApplicable(Environment $environment): bool
    {
        return true;
    }
}
