<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Artifact;

/**
 * Class ilCtrlPluginStructureArtifactObjective
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This artifact is a necessary redundancy in order to allow executing
 * small ctrl structure reloads when plugins are installed, uninstalled
 * or updated.
 *
 * Due to this artifact, there is a possibility that classes which were
 * removed from the plugin structure during a plugin-update are still
 * contained in the ctrl structure artifact. To resolve this problem a
 * `composer du` is needed.
 */
class ilCtrlPluginStructureArtifactObjective extends BuildArtifactObjective
{
    /**
     * @var string relative path to the php artifact file.
     */
    public const ARTIFACT_PATH = "./Services/UICore/artifacts/ctrl_plugin_structure.php";

    /**
     * Holds the currently read plugin structure if it exists.
     *
     * @var array
     */
    private array $plugin_structure;

    /**
     * Holds the currently read ctrl structure.
     *
     * @var array
     */
    private array $ctrl_structure;

    /**
     * ilCtrlPluginStructureArtifactObjective Constructor
     */
    public function __construct()
    {
        $ilias_path = dirname(__FILE__, 6) . '/';

        $this->ctrl_structure = require $ilias_path . ilCtrlStructureArtifactObjective::ARTIFACT_PATH;

        $absolute_artifact_path = $ilias_path . self::ARTIFACT_PATH;
        if (file_exists($absolute_artifact_path)) {
            $this->plugin_structure = require $absolute_artifact_path;
        } else {
            $this->plugin_structure = [];
        }
    }

    /**
     * @inheritDoc
     */
    public function getArtifactPath() : string
    {
        return self::ARTIFACT_PATH;
    }

    /**
     * Returns an artifact which contains the ctrl structure of all
     * plugin-classes mapped to their plugin id.
     *
     * @inheritDoc
     */
    public function build() : Artifact
    {
        $data = [];
        foreach (new ilCtrlPluginIterator() as $plugin_id => $plugin_dir) {
            $data[$plugin_id] = (new ilCtrlStructureReader(
                new ilCtrlDirectoryIterator($plugin_dir),
                new ilCtrlStructureCidGenerator(
                    $this->getNextStructureIndex()
                )
            ))->readStructure();
        }

        return new ArrayArtifact($data);
    }

    /**
     * Returns an artifact which contains the existing plugin ctrl structure
     * and updates the classes for the plugin instance provided by the
     * environment.
     *
     * Note that the environment MUST contain an @see ilCtrlPluginArtifactConfig
     * object in order to execute a structure reload for a plugin.
     *
     * @inheritDoc
     *
     * @throws ilCtrlException if the environment wasn't configured properly.
     */
    public function buildIn(Environment $env) : Artifact
    {
        // if the environment DOES NOT contain a config for this
        // objective, the normal build method is called in order
        // to achieve this objective via CLI as well.
        if (!$env->hasConfigFor(self::class)) {
            return $this->build();
        }

        $config = $env->getConfigFor(self::class);
        if (!$config instanceof ilCtrlPluginArtifactConfig) {
            throw new ilCtrlException("Configuration for component '" . self::class . "' must contain an instance of " . ilCtrlPluginArtifactConfig::class);
        }

        $plugin = $config->getPlugin();

        // unset the plugin's structure entry, as we don't
        // want class-entries for (possibly) removed classes.
        unset($this->plugin_structure[$plugin->getId()]);

        if (ilCtrlPluginArtifactConfig::PLUGIN_STATUS_UNINSTALL === $config->getStatus()) {
            return new ArrayArtifact(
                $this->reindexPluginStructure()
            );
        }

        $this->plugin_structure[$plugin->getId()] = (new ilCtrlStructureReader(
            new ilCtrlDirectoryIterator($plugin->getDirectory()),
            new ilCtrlStructureCidGenerator(
                $this->getNextPluginStructureIndex()
            )
        ))->readStructure();

        return new ArrayArtifact(
            $this->reindexPluginStructure()
        );
    }

    /**
     * Returns the current plugins structure re-indexed from the
     * last possible ctrl structure index.
     *
     * @return array<string, mixed>
     */
    private function reindexPluginStructure() : array
    {
        if (empty($this->plugin_structure)) {
            return [];
        }

        $generator = new ilCtrlStructureCidGenerator($this->getNextStructureIndex());
        foreach ($this->plugin_structure as $plugin_id => $plugin_data) {
            if (!empty($plugin_data)) {
                foreach ($plugin_data as $class_name => $data) {
                    $this->plugin_structure[$plugin_id][$class_name][ilCtrlStructureInterface::KEY_CLASS_CID] = $generator->getCid();
                }
            }
        }

        return $this->plugin_structure;
    }

    /**
     * Returns the next index that can be used from the current
     * plugin structure.
     *
     * @return int
     */
    private function getNextPluginStructureIndex() : int
    {
        if (!empty($this->plugin_structure)) {
            $last_plugin_entry = $this->plugin_structure[array_key_last($this->plugin_structure)];
            $last_array_entry = $last_plugin_entry[array_key_last($last_plugin_entry)];
            $last_cid = $last_array_entry[ilCtrlStructureInterface::KEY_CLASS_CID];

            return (new ilCtrlStructureCidGenerator())->getIndexByCid($last_cid) + 1;
        }

        return $this->getNextStructureIndex();
    }

    /**
     * Returns the next index that can be used from the current
     * ctrl structure.
     *
     * @return int
     */
    private function getNextStructureIndex() : int
    {
        if (!empty($this->ctrl_structure)) {
            $last_array_entry = $this->ctrl_structure[array_key_last($this->ctrl_structure)];
            $last_cid = $last_array_entry[ilCtrlStructureInterface::KEY_CLASS_CID];

            return (new ilCtrlStructureCidGenerator())->getIndexByCid($last_cid) + 1;
        }

        return 0;
    }
}