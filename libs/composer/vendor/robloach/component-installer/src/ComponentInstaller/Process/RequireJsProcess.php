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

use Assetic\Asset\StringAsset;
use Composer\Config;
use Composer\Json\JsonFile;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;

/**
 * Builds the require.js configuration.
 */
class RequireJsProcess extends Process
{
    /**
     * The base URL for the require.js configuration.
     */
    protected $baseUrl = 'components';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $output = parent::init();
        if ($this->config->has('component-baseurl')) {
            $this->baseUrl = $this->config->get('component-baseurl');
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        // Construct the require.js and stick it in the destination.
        $json = $this->requireJson($this->packages, $this->config);
        $requireConfig = $this->requireJs($json);

        // Attempt to write the require.config.js file.
        $destination = $this->componentDir . '/require.config.js';
        $this->fs->ensureDirectoryExists(dirname($destination));
        if (file_put_contents($destination, $requireConfig) === FALSE) {
            $this->io->write('<error>Error writing require.config.js</error>');

            return false;
        }

        // Read in require.js to prepare the final require.js.
        if (!file_exists(dirname(__DIR__) . '/Resources/require.js')) {
            $this->io->write('<error>Error reading in require.js</error>');

            return false;
        }

        $assets = $this->newAssetCollection();
        $assets->add(new FileAsset(dirname(__DIR__) . '/Resources/require.js'));
        $assets->add(new StringAsset($requireConfig));

        // Append the config to the require.js and write it.
        if (file_put_contents($this->componentDir . '/require.js', $assets->dump()) === FALSE) {
            $this->io->write('<error>Error writing require.js to the components directory</error>');

            return false;
        }

        return null;
    }

    /**
     * Creates a require.js configuration from an array of packages.
     *
     * @param $packages
     *   An array of packages from the composer.lock file.
     *
     * @return array
     *   The built JSON array.
     */
    public function requireJson(array $packages)
    {
        $json = array();

        // Construct the packages configuration.
        foreach ($packages as $package) {
            // Retrieve information from the extra options.
            $extra = isset($package['extra']) ? $package['extra'] : array();
            $options = isset($extra['component']) ? $extra['component'] : array();

            // Construct the base details.
            $name = $this->getComponentName($package['name'], $extra);
            $component = array(
                'name' => $name,
            );

            // Build the "main" directive.
            $scripts = isset($options['scripts']) ? $options['scripts'] : array();
            if (!empty($scripts)) {
                // Put all scripts into a build.js file.
                $result = $this->aggregateScripts($package, $scripts, $name.DIRECTORY_SEPARATOR.$name.'-built.js');
                if ($result) {
                    // If the aggregation was successful, add the script to the
                    // packages array.
                    $component['main'] = $name.'-built.js';

                    // Add the component to the packages array.
                    $json['packages'][] = $component;
                }
            }

            // Add the shim definition for the package.
            $shim = isset($options['shim']) ? $options['shim'] : array();
            if (!empty($shim)) {
                $json['shim'][$name] = $shim;
            }

            // Add the config definition for the package.
            $packageConfig = isset($options['config']) ? $options['config'] : array();
            if (!empty($packageConfig)) {
                $json['config'][$name] = $packageConfig;
            }
        }

        // Provide the baseUrl.
        $json['baseUrl'] = $this->baseUrl;

        // Merge in configuration options from the root.
        if ($this->config->has('component')) {
            $config = $this->config->get('component');
            if (isset($config) && is_array($config)) {
                // Use a recursive, distict array merge.
                $json = $this->arrayMergeRecursiveDistinct($json, $config);
            }
        }

        return $json;
    }

    /**
     * Concatenate all scripts together into one destination file.
     *
     * @param array $package
     * @param array $scripts
     * @param string $file
     * @return bool
     */
    public function aggregateScripts($package, array $scripts, $file)
    {
        $assets = $this->newAssetCollection();

        foreach ($scripts as $script) {
            // Collect each candidate from a glob file search.
            $path = $this->getVendorDir($package).DIRECTORY_SEPARATOR.$script;
            $matches = $this->fs->recursiveGlobFiles($path);
            foreach ($matches as $match) {
                $assets->add(new FileAsset($match));
            }
        }
        $js = $assets->dump();

        // Write the file if there are any JavaScript assets.
        if (!empty($js)) {
            $destination = $this->componentDir.DIRECTORY_SEPARATOR.$file;
            $this->fs->ensureDirectoryExists(dirname($destination));

            return file_put_contents($destination, $js);
        }

        return false;
    }

    /**
     * Constructs the require.js file from the provided require.js JSON array.
     *
     * @param $json
     *   The require.js JSON configuration.
     *
     * @return string
     *   The RequireJS JavaScript configuration.
     */
    public function requireJs(array $json = array())
    {
        // Encode the array to a JSON array.
        $js = JsonFile::encode($json);

        // Construct the JavaScript output.
        $output = <<<EOT
var components = $js;
if (typeof require !== "undefined" && require.config) {
    require.config(components);
} else {
    var require = components;
}
if (typeof exports !== "undefined" && typeof module !== "undefined") {
    module.exports = components;
}
EOT;

        return $output;
    }

    /**
     * Merges two arrays without changing string array keys. Appends to array if keys are numeric.
     *
     * @see array_merge()
     * @see array_merge_recursive()
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected function arrayMergeRecursiveDistinct(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if(is_numeric($key)){
                $merged[] = $value;
            } else {
                if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                    $merged[$key] = $this->arrayMergeRecursiveDistinct($merged[$key], $value);
                }
                else {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }

    /**
     * @return AssetCollection
     */
    protected function newAssetCollection()
    {
        // Aggregate all the assets into one file.
        $assets = new AssetCollection();
        if ($this->config->has('component-scriptFilters')) {
            $filters = $this->config->get('component-scriptFilters');
            if (isset($filters) && is_array($filters)) {
                foreach ($filters as $filter => $filterParams) {
                    $reflection = new \ReflectionClass($filter);
                    /** @var \Assetic\Filter\FilterInterface $filter */
                    $filter = $reflection->newInstanceArgs($filterParams);
                    $assets->ensureFilter($filter);
                }
            }
        }

        return $assets;
    }
}
