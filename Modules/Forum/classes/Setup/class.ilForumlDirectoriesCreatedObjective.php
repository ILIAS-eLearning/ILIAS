<?php

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilForumlDirectoriesCreatedObjective implements Setup\Objective
{
    const FORUMDIR = 'forum';

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Forum-directory is created";
    }

    public function isNotable() : bool
    {
        return false;
    }

    protected function getDirectories(Setup\Environment $environment): array
    {
        $common_config = $environment->getConfigFor("common");
        $fs_config = $environment->getConfigFor("filesystem");

        $data_dir = $fs_config->getDataDir();
        $client_data_dir = $data_dir .'/' .$common_config->getClientId();
        $new_dir = $client_data_dir .'/' .self::FORUMDIR;

        return [
            $data_dir,
            $client_data_dir,
            $new_dir
        ];
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        $ret = [];
        foreach ($this->getDirectories($environment) as $dir) {
            $ret[] = new Setup\DirectoryCreatedObjective($dir);
        }
        return $ret;
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        list($data, $client, $new_dir) = $this->getDirectories($environment);
        if (! is_dir($new_dir)) {
            throw new Setup\UnachievableException("Could not create directory for Forum");
        }

        return $environment;
    }
}
