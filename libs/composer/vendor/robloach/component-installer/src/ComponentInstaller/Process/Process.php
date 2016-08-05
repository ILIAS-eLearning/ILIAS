<?php

/*
 * This file is part of Component Installer.
 *
 * (c) Rob Loach (http://robloach.net)
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace ComponentInstaller\Process;

use Composer\IO\IOInterface;
use Composer\Composer;
use Composer\IO\NullIO;
use Composer\Package\Dumper\ArrayDumper;
use ComponentInstaller\Util\Filesystem;
use Composer\Package\Loader\ArrayLoader;

/**
 * The base Process type.
 *
 * Processes are initialized, and then run during installation.
 */
class Process implements ProcessInterface
{
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface|NullIO
     */
    protected $io;

    /**
     * @var \Composer\Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $packages = array();

    /**
     * @var string
     */
    protected $componentDir = 'components';

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * The Composer installation manager to find Component vendor directories.
     * @var \Composer\Installer\InstallationManager
     */
    protected $installationManager;

    /**
     * {@inheritdoc}
     */
    public function __construct(Composer $composer = null, IOInterface $io = null)
    {
        $this->composer = isset($composer) ? $composer : new Composer();
        $this->io = isset($io) ? $io : new NullIO();
        $this->fs = new Filesystem();
        $this->installationManager = $this->composer->getInstallationManager();
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // Retrieve the configuration variables.
        $this->config = $this->composer->getConfig();
        if (isset($this->config)) {
            if ($this->config->has('component-dir')) {
                $this->componentDir = $this->config->get('component-dir');
            }
        }

        // Get the available packages.
        $allPackages = array();
        /** @var \Composer\Package\Locker $locker */
        $locker = $this->composer->getLocker();
        if ($locker !== null && $locker->isLocked()) {
            $lockData = $locker->getLockData();
            $allPackages = $lockData['packages'];

            // Also merge in any of the development packages.
            $dev = isset($lockData['packages-dev']) ? $lockData['packages-dev'] : array();
            foreach ($dev as $package) {
                $allPackages[] = $package;
            }
        }

        // Only add those packages that we can reasonably
        // assume are components into our packages list
        /** @var \Composer\Package\RootPackageInterface $rootPackage */
        $rootPackage = $this->composer->getPackage();
        $rootExtras = $rootPackage ? $rootPackage->getExtra() : array();
        $customComponents = isset($rootExtras['component']) ? $rootExtras['component'] : array();
        foreach ($allPackages as $package) {
            $name = $package['name'];
            if (isset($customComponents[$name]) && is_array($customComponents[$name])) {
                $package['extra'] = array('component' => $customComponents[$name]);
                $this->packages[] = $package;
            }
            else {
                $extra = isset($package['extra']) ? $package['extra'] : array();
                if (isset($extra['component']) && is_array($extra['component'])) {
                    $this->packages[] = $package;
                }
            }
        }

        // Add the root package to the packages list.
        $root = $this->composer->getPackage();
        if ($root) {
            $dumper = new ArrayDumper();
            $package = $dumper->dump($root);
            $package['is-root'] = true;
            $this->packages[] = $package;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        return false;
    }

    /**
     * Retrieves the component name for the component.
     *
     * @param string $prettyName
     *   The Composer package name.
     * @param array $extra
     *   The extra config options sent from Composer.
     *
     * @return string
     *   The name of the component, without its vendor name.
     */
    public function getComponentName($prettyName, array $extra = array())
    {
        // Parse the pretty name for the vendor and name.
        if (strpos($prettyName, '/') !== false) {
            list($vendor, $name) = explode('/', $prettyName);
            unset($vendor);
        } else {
            // Vendor wasn't found, so default to the pretty name instead.
            $name = $prettyName;
        }

        // Allow the component to define its own name.
        $component = isset($extra['component']) ? $extra['component'] : array();
        if (isset($component['name'])) {
            $name = $component['name'];
        }

        return $name;
    }

    /**
     * Retrieves the component directory.
     */
    public function getComponentDir()
    {
        return $this->componentDir;
    }

    /**
     * Sets the component directory.
     * @param string $dir
     * @return string
     */
    public function setComponentDir($dir)
    {
        return $this->componentDir = $dir;
    }

    /**
     * Retrieves the given package's vendor directory, where it's installed.
     *
     * @param array $package
     *   The package to retrieve the vendor directory for.
     * @return string
     */
    public function getVendorDir(array $package)
    {
        // The root package vendor directory is not handled by getInstallPath().
        if (isset($package['is-root']) && $package['is-root'] === true) {
            $path = getcwd();

            if (!file_exists($path.DIRECTORY_SEPARATOR.'composer.json')) {
                for ($temp = __DIR__; strlen($temp) > 3; $temp = dirname($temp)) {
                    if (file_exists($temp.DIRECTORY_SEPARATOR.'composer.json')) {
                        $path = $temp;
                    }
                }
            }

            return $path;
        }

        if (!isset($package['version'])) {
            $package['version'] = '1.0.0';
        }
        $loader = new ArrayLoader();
        $completePackage = $loader->load($package);

        return $this->installationManager->getInstallPath($completePackage);
    }
}
