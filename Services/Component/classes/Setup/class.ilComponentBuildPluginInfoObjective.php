<?php

use ILIAS\Setup;

class ilComponentBuildPluginInfoObjective extends Setup\Artifact\BuildArtifactObjective
{
    const BASE_PATH = "./Customizing/global/plugins/";
    const PLUGIN_PHP = "plugin.php";

    public function getArtifactPath() : string
    {
        return \ilArtifactComponentDataDB::PLUGIN_DATA_PATH;
    }


    public function build() : Setup\Artifact
    {
        $data = [];
        foreach (["Modules", "Services"] as $type) {
            $components = $this->scanDir(static::BASE_PATH . "$type");
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

    protected function addPlugin(array &$data, string $type, string $component, string $slot, string $plugin) : void
    {
        $path = static::BASE_PATH . "$type/$component/$slot/$plugin/" . static::PLUGIN_PHP;
        $file = $this->readFile($path);
        if (is_null($file)) {
            throw new \RuntimeException(
                "Cannot read $path."
            );
        }

        eval("?>" . $file);
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
    protected function scanDir(string $dir) : array
    {
        if (!file_exists($dir)) {
            return [];
        }
        $result = scandir($dir);
        return array_values(array_diff($result, [".", ".."]));
    }

    protected function readFile(string $path) : ?string
    {
        if (!file_exists($path) || !is_file($path)) {
            return null;
        }
        return file_get_contents($path);
    }
}
