<?php

/*
 * This file is part of Component Installer.
 *
 * (c) Rob Loach (http://robloach.net)
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace ComponentInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Composer Plugin to install Components.
 *
 * Adds the ComponentInstaller Plugin to the Composer instance.
 *
 * @see ComponentInstaller\Installer
 */
class ComponentInstallerPlugin implements PluginInterface
{
    /**
     * Called when the plugin is activated.
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new Installer($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}
