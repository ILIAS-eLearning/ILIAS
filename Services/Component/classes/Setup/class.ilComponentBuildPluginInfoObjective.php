<?php

use ILIAS\Setup;

class ilComponentBuildPluginInfoObjective extends Setup\Artifact\BuildArtifactObjective
{
    protected const BASE_PATH = "./Customizing/global/plugins/";
    protected const PLUGIN_PHP = "plugin.php";
    protected const PLUGIN_CLASS_FILE = "classes/class.il%sPlugin.php";

    public function getArtifactPath(): string
    {
        return \ilArtifactComponentRepository::PLUGIN_DATA_PATH;
    }


    public function build(): Setup\Artifact
    {
        $data = [];
        foreach (["Modules", "Services"] as $type) {
            $components = $this->scanDir(static::BASE_PATH . $type);
            foreach ($components as $component) {
                $slots = $this->scanDir(static::BASE_PATH . "$type/$component");
                foreach ($slots as $slot) {
                    $plugins = $this->scanDir(static::BASE_PATH . "$type/$component/$slot");
                    foreach ($plugins as $plugin) {
                        $this->addPlugin($data, $type, $component, $slot, $plugin);
                    }
                }
            }
        }
        return new Setup\Artifact\ArrayArtifact($data);
    }

    protected function addPlugin(array &$data, string $type, string $component, string $slot, string $plugin): void
    {
        $path = static::BASE_PATH . "$type/$component/$slot/$plugin/" . static::PLUGIN_PHP;
        $plugin_php = $this->readFile($path);
        if (is_null($plugin_php)) {
            throw new \RuntimeException(
                "Cannot read plugin.php of $type/$component/$slot/$plugin at $path."
            );
        }

        $path = static::BASE_PATH . "$type/$component/$slot/$plugin/" . sprintf(static::PLUGIN_CLASS_FILE, $plugin);
        if (is_null($this->readFile($path))) {
            throw new \RuntimeException(
                "Cannot read plugin class file of $type/$component/$slot/$plugin at $path."
            );
        }

        eval("?>" . $plugin_php);
        if (!isset($id)) {
            throw new \InvalidArgumentException("$path does not define \$id");
        }
        if (!isset($version)) {
            throw new \InvalidArgumentException("$path does not define \$version");
        }
        if (!isset($ilias_min_version)) {
            throw new \InvalidArgumentException("$path does not define \$ilias_min_version");
        }
        if (!isset($ilias_max_version)) {
            throw new \InvalidArgumentException("$path does not define \$ilias_max_version");
        }

        if (isset($data[$id])) {
            throw new \RuntimeException(
                "Plugin with id $id already exists."
            );
        }

        $data[$id] = [
            $type,
            $component,
            $slot,
            $plugin,
            $version,
            $ilias_min_version,
            $ilias_max_version,
            $responsible ?? "",
            $responsible_mail ?? "",
            $learning_progress ?? null,
            $supports_export ?? null,
            $supports_cli_setup ?? null
        ];
    }

    /**
     * @return string[]
     */
    protected function scanDir(string $dir): array
    {
        if (!file_exists($dir)) {
            return [];
        }
        $result = scandir($dir);
        return array_values(array_diff($result, [".", ".."]));
    }

    protected function readFile(string $path): ?string
    {
        if (!file_exists($path) || !is_file($path)) {
            return null;
        }
        return file_get_contents($path);
    }
}
