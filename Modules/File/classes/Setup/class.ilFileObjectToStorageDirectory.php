<?php

/**
 * Class ilFileObjectToStorageDirectory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileObjectToStorageDirectory
{
    /**
     * @var int
     */
    protected $object_id;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var ilFileObjectToStorageVersion[]
     */
    protected $versions = [];

    /**
     * ilFileObjectToStorageDirectory constructor.
     * @param int    $object_id
     * @param string $path
     */
    public function __construct(int $object_id, string $path)
    {
        $this->object_id = $object_id;
        $this->path = $path;
        $this->initVersions();
    }

    private function initVersions() : void
    {
        $history_data = $this->getHistoryData();
        try {
            $g = new RegexIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(
                        $this->path,
                        FilesystemIterator::KEY_AS_PATHNAME
                        |FilesystemIterator::CURRENT_AS_FILEINFO
                        |FilesystemIterator::SKIP_DOTS
                    ),
                    RecursiveIteratorIterator::LEAVES_ONLY
                ),
                '/.*\/file_[\d]*\/([\d]*)\/(.*)/',
                RegexIterator::GET_MATCH
            );
        } catch (Throwable $t) {
            // there was an error reading the directory, there will be no versions
            $g = [];
        }
        
        
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

    /**
     * @return array
     */
    private function getHistoryData() : array
    {
        $info = ilHistory::_getEntriesForObject($this->object_id, 'file');
        $history_data = [];
        foreach ($info as $i) {
            $parsed_info = ilObjFileImplementationLegacy::parseInfoParams($i);
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
    public function getVersions() : Generator
    {
        yield from $this->versions;
    }

    /**
     * @return int
     */
    public function getObjectId() : int
    {
        return $this->object_id;
    }

    public function tearDown() : void
    {
        if (is_writable($this->path)) {
            touch(rtrim($this->path, "/") . "/" . ilFileObjectToStorageMigrationHelper::MIGRATED);
        }
    }
}
