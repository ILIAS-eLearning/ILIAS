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

/**
 * Class ilFileObjectToStorageDirectory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileObjectToStorageDirectory
{
    protected int $object_id;
    protected string $path;
    /**
     * @var ilFileObjectToStorageVersion[]
     */
    protected array $versions = [];

    /**
     * ilFileObjectToStorageDirectory constructor.
     * @param int $object_id
     * @param string $path
     */
    public function __construct(int $object_id, string $path)
    {
        $this->object_id = $object_id;
        $this->path = $path;
        $this->initVersions();
    }

    private function initVersions(): void
    {
        $history_data = $this->getHistoryData();

        $g = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $this->path,
                    FilesystemIterator::KEY_AS_PATHNAME
                    | FilesystemIterator::CURRENT_AS_FILEINFO
                    | FilesystemIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::LEAVES_ONLY
            ),
            '/.*\/file_[\d]*\/([\d]*)\/(.*)/',
            RegexIterator::GET_MATCH
        );

        $this->versions = [];

        foreach ($g as $item) {
            $version = (int) $item[1];
            $title = $history_data[$version]['filename'] ?? $item[2];
            $action = $history_data[$version]['action'] ?? 'create';
            $owner = $history_data[$version]['owner_id'] ?? 13;
            $creation_date_timestamp = strtotime($history_data[$version]['date'] ?? '0');
            if ($creation_date_timestamp === false) {
                $creation_date_timestamp = 0;
            }
            $this->versions[$version] = new ilFileObjectToStorageVersion(
                $version,
                $item[0],
                $title,
                $title,
                $action,
                $creation_date_timestamp,
                $owner
            );
        }
        ksort($this->versions);
    }


    private function getHistoryData(): array
    {
        $info = ilHistory::_getEntriesForObject($this->object_id, 'file');
        $history_data = [];
        foreach ($info as $i) {
            $parsed_info = self::parseInfoParams($i);
            $version = (int) $parsed_info['version'];
            $history_data[$version] = $parsed_info;
            $history_data[$version]['owner_id'] = (int) $i['user_id'];
            $history_data[$version]['date'] = (string) $i['date'];
            $history_data[$version]['action'] = (string) $i['action'];
        }

        uasort($history_data, static function ($v1, $v2) {
            return (int) $v2["version"] - (int) $v1["version"];
        });
        return $history_data;
    }

    /**
     * @return Generator|ilFileObjectToStorageVersion[]
     */
    public function getVersions(): Generator
    {
        yield from $this->versions;
    }

    /**
     * @return int
     */
    public function getObjectId(): int
    {
        return $this->object_id;
    }

    public function tearDown(): void
    {
        touch(rtrim($this->path, "/") . "/" . ilFileObjectToStorageMigrationHelper::MIGRATED);
    }

    public static function parseInfoParams(array $entry): array
    {
        $data = explode(",", $entry["info_params"]);

        // bugfix: first created file had no version number
        // this is a workaround for all files created before the bug was fixed
        if (empty($data[1])) {
            $data[1] = "1";
        }

        if (empty($data[2])) {
            $data[2] = "1";
        }

        // BEGIN bugfix #31730
        // if more than 2 commas are detected, the need for reassembling the filename is: possible to necessary
        if (sizeof($data) > 2) {
            $last = sizeof($data) - 1;
            for ($n = 1; $n < $last - 1; $n++) {
                $data[0] .= "," . $data[$n];
            }

            // trying to distinguish the next-to-last being a 'last part of the filename'
            // or a 'version information',  based on having a dot included or not
            if (strpos($data[$last - 1], ".") !== false) {
                $data[0] .= "," . $data[$last - 1];
                $data[1] = $data[$last];
                $data[2] = $data[$last];
            } else {
                $data[1] = $data[$last - 1];
                $data[2] = $data[$last];
            }
        }
        // END bugfix #31730

        $result = array(
            "filename" => $data[0],
            "version" => $data[1],
            "max_version" => $data[2],
            "rollback_version" => "",
            "rollback_user_id" => "",
        );

        // if rollback, the version contains the rollback version as well
        if ($entry["action"] == "rollback") {
            $tokens = explode("|", $result["max_version"]);
            if (count($tokens) > 1) {
                $result["max_version"] = $tokens[0];
                $result["rollback_version"] = $tokens[1];

                if (count($tokens) > 2) {
                    $result["rollback_user_id"] = $tokens[2];
                }
            }
        }

        return $result;
    }
}
