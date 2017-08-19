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

use Composer\Config;
use Assetic\Asset\AssetCollection;
use Assetic\Filter\CssRewriteFilter;
use Assetic\Asset\FileAsset;
use Assetic\Filter\FilterCollection;

/**
 * Builds the require.css file from all Component stylesheets.
 */
class RequireCssProcess extends Process
{
    /**
     * {@inheritdoc}
     */
    public function process()
    {
        $filters = array(new CssRewriteFilter());
        if ($this->config->has('component-styleFilters')) {
            $customFilters = $this->config->get('component-styleFilters');
            if (isset($customFilters) && is_array($customFilters)) {
                foreach ($customFilters as $filter => $filterParams) {
                    $reflection = new \ReflectionClass($filter);
                    $filters[] = $reflection->newInstanceArgs($filterParams);
                }
            }
        }

        $filterCollection = new FilterCollection($filters);

        $assets = new AssetCollection();
        $styles = $this->packageStyles($this->packages);
        foreach ($styles as $package => $packageStyles) {
            $packageAssets = new AssetCollection();
            $packagePath = $this->componentDir.'/'.$package;

            foreach ($packageStyles as $style => $paths) {
                foreach ($paths as $path) {
                    // The full path to the CSS file.
                    $assetPath = realpath($path);
                    // The root of the CSS file.
                    $sourceRoot = dirname($path);
                    // The style path to the CSS file when external.
                    $sourcePath = $package . '/' . $style;
                    //Replace glob patterns with filenames.
                    $filename = basename($style);
                    if(preg_match('~^\*(\.[^\.]+)$~', $filename, $matches)){
                        $sourcePath = str_replace($filename, basename($assetPath), $sourcePath);
                    }
                    // Where the final CSS will be generated.
                    $targetPath = $this->componentDir;
                    // Build the asset and add it to the collection.
                    $asset = new FileAsset($assetPath, $filterCollection, $sourceRoot, $sourcePath);
                    $asset->setTargetPath($targetPath);
                    $assets->add($asset);
                    // Add asset to package collection.
                    $sourcePath = preg_replace('{^.*'.preg_quote($package).'/}', '', $sourcePath);
                    $asset = new FileAsset($assetPath, $filterCollection, $sourceRoot, $sourcePath);
                    $asset->setTargetPath($packagePath);
                    $packageAssets->add($asset);
                }
            }

            if (file_put_contents($packagePath.'/'.$package.'-built.css', $packageAssets->dump()) === FALSE) {
                $this->io->write("<error>Error writing $package-built.css to destination</error>");
            }
        }

        if (file_put_contents($this->componentDir . '/require.css', $assets->dump()) === FALSE) {
            $this->io->write('<error>Error writing require.css to destination</error>');
            return false;
        }

        return null;
    }

    /**
     * Retrieves an array of styles from a collection of packages.
     *
     * @param array $packages
     *   An array of packages from the composer.lock file.
     *
     * @return array
     *   A set of package styles.
     */
    public function packageStyles(array $packages)
    {
        $output = array();

        // Construct the packages configuration.
        foreach ($packages as $package) {
            // Retrieve information from the extra options.
            $extra = isset($package['extra']) ? $package['extra'] : array();
            $name = $this->getComponentName($package['name'], $extra);
            $component = isset($extra['component']) ? $extra['component'] : array();
            $styles = isset($component['styles']) ? $component['styles'] : array();
            $vendorDir = $this->getVendorDir($package);

            // Loop through each style.
            foreach ($styles as $style) {
                // Find the style path from the vendor directory.
                $path = strtr($vendorDir.'/'.$style, '/', DIRECTORY_SEPARATOR);

                // Search for the candidate with a glob recursive file search.
                $files = $this->fs->recursiveGlobFiles($path);
                foreach ($files as $file) {
                    // Provide the package name, style and full path.
                    $output[$name][$style][] = $file;
                }
            }
        }

        return $output;
    }
}
