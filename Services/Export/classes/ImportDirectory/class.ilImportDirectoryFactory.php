<?php

declare(strict_types=1);

use ILIAS\Filesystem\Filesystem;

class ilImportDirectoryFactory
{
    public const TYPE_MOB = 'mob';
    public const TYPE_SAHS = 'sahs';
    public const TYPE_EXPORT = 'export';

    protected ilLogger $logger;
    protected Filesystem $storage_directory;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->exp();
        $this->storage_directory = $DIC->filesystem()->storage();
    }

    public function getInstanceForComponent(string $type): ilImportDirectory
    {
        switch ($type) {
            case self::TYPE_MOB:
                $instance = new ilMediaObjectImportDirectory(
                    $this->storage_directory,
                    $this->logger
                );
                break;

            case self::TYPE_SAHS:
                $instance = new ilScormImportDirectory(
                    $this->storage_directory,
                    $this->logger
                );
                break;

            case self::TYPE_EXPORT:
                $instance = new ilExportImportDirectory(
                    $this->storage_directory,
                    $this->logger
                );
                break;

            default:
                $this->logger->error('Invalid type given: ' . $type);
                throw new DomainException(
                    'Invalid type given: ' . $type
                );
        }
        return $instance;
    }
}
