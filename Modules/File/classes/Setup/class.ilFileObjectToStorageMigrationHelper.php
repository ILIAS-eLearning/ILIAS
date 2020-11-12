<?php

class ilFileObjectToStorageMigrationHelper
{
    protected $base_path = '/var/iliasdata/ilias/default/ilFile';
    /**
     * @var Iterator
     */
    protected $iterator;
    /**
     * @var int
     */
    private $remaining;

    public const MIGRATED = ".migrated";

    /**
     * ilFileObjectToStorageMigrationHelper constructor.
     * @param string $base_path
     * @param string $regex
     */
    public function __construct(string $base_path, string $regex)
    {
        $this->base_path = $base_path;
        $this->iterator = new RecursiveDirectoryIterator(
            $base_path,
            FilesystemIterator::KEY_AS_PATHNAME
            |FilesystemIterator::CURRENT_AS_FILEINFO
            |FilesystemIterator::SKIP_DOTS
        );
        $this->iterator = new RecursiveIteratorIterator($this->iterator, RecursiveIteratorIterator::SELF_FIRST);
        $this->iterator = new RegexIterator($this->iterator, $regex, RegexIterator::GET_MATCH);
        $this->iterator = new CallbackFilterIterator($this->iterator, static function ($path) {
            return is_readable($path[0]) && !file_exists(rtrim($path[0], "/") . "/" . self::MIGRATED);
        });

        $this->remaining = iterator_count($this->iterator);
        $this->iterator->rewind();
    }

    public function rewind() : void
    {
        $this->iterator->rewind();
    }

    public function getAmountOfItems() : int
    {
        return $this->remaining;
    }

    public function getNext() : ilFileObjectToStorageDirectory
    {
        $item = $this->iterator->current();
        $this->iterator->next();

        return new ilFileObjectToStorageDirectory((int) $item[1], $item[0]);
    }

}
