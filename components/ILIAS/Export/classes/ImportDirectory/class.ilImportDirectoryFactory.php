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
