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

namespace ILIAS\Export\ExportHandler\Repository\Wrapper\IRSS;

use DateTimeImmutable;
use ilFileUtils;
use ILIAS\Export\ExportHandler\I\Info\Export\HandlerInterface as ilExportHandlerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Repository\Stakeholder\HandlerInterface as ilExportHandlerRepositoryStakeholderInterface;
use ILIAS\Export\ExportHandler\I\Repository\Wrapper\IRSS\HandlerInterface as ilExportHandlerRepositoryIRSSWrapperInterface;
use ILIAS\Filesystem\Filesystems;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\Archive\Zip;
use ILIAS\Filesystem\Util\Archive\ZipOptions;
use ILIAS\ResourceStorage\Services as ResourcesStorageService;
use SplFileInfo;

class Handler implements ilExportHandlerRepositoryIRSSWrapperInterface
{
    protected ResourcesStorageService $irss;
    protected Filesystems $filesystems;

    public function __construct(
        ResourcesStorageService $irss,
        Filesystems $filesystems
    ) {
        $this->irss = $irss;
        $this->filesystems = $filesystems;
    }

    public function createEmptyContainer(
        ilExportHandlerExportInfoInterface $info,
        ilExportHandlerRepositoryStakeholderInterface $stakeholder
    ): string {
        $tmp_dir_info = new SplFileInfo(ilFileUtils::ilTempnam());
        $this->filesystems->temp()->createDir($tmp_dir_info->getFilename());
        $export_dir = $tmp_dir_info->getRealPath();
        $options = (new ZipOptions())
            ->withZipOutputName($info->getZipFileName())
            ->withZipOutputPath($export_dir);
        $zip = new Zip(
            $options,
        );
        $zip->addStream(Streams::ofString(self::TMP_FILE_CONTENT), self::TMP_FILE_PATH);
        $rid = $this->irss->manageContainer()->containerFromStream($zip->get(), $stakeholder);
        ilFileUtils::delDir($export_dir);
        return $rid->serialize();
    }

    public function getCreationDate(
        string $resource_identification_serialized
    ): DateTimeImmutable {
        $resource_identification = $this->irss->manageContainer()->find($resource_identification_serialized);
        return $this->irss->manageContainer()->getResource($resource_identification)->getCurrentRevision()
            ->getInformation()->getCreationDate();
    }

    public function removeContainer(
        string $resource_identification_serialized,
        ilExportHandlerRepositoryStakeholderInterface $stakeholder
    ): bool {
        $resource_identification = $this->irss->manageContainer()->find($resource_identification_serialized);
        if (is_null($resource_identification)) {
            return false;
        }
        $this->irss->manageContainer()->remove(
            $resource_identification,
            $stakeholder
        );
        return true;
    }
}
