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

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Types\ListType;
use ILIAS\BackgroundTasks\Types\TupleType;

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCalendarCopyFilesToTempDirectoryJob extends AbstractJob
{
    private ilLogger $logger;
    private ILIAS\ResourceStorage\Services $irss;

    protected string $target_directory;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->irss = $DIC->resourceStorage();
        $this->logger = $DIC->logger()->cal();
    }

    /**
     * @inheritDoc
     */
    public function getInputTypes(): array
    {
        return
            [
                new SingleType(ilCalendarRessourceStorageCopyDefinition::class)
            ];
    }

    /**
     * @inheritDoc
     * @todo output should be file type
     */
    public function getOutputType(): Type
    {
        return new SingleType(StringValue::class);
    }

    /**
     * @inheritDoc
     */
    public function isStateless(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function run(array $input, Observer $observer): Value
    {
        $cal_copy_def = $input[0];

        $this->logger->info('Called copy files job');

        $this->target_directory = $cal_copy_def->getTempDir();
        //$this->target_directory = $input[1]->getValue();

        // create temp directory
        $tmpdir = $this->createUniqueTempDirectory();
        $targetdir = $this->createTargetDirectory($tmpdir);

        // copy files from source to temp directory
        //$this->copyFiles($targetdir, $input[0]);
        $this->copyFiles($targetdir, $cal_copy_def);

        // zip

        // return zip file name
        $this->logger->debug('Returning new tempdirectory: ' . $targetdir);

        $out = new StringValue();
        $out->setValue($targetdir);
        return $out;
    }

    /**
     * @return string absolute path to new temp directory
     * @todo refactor to new file system access
     * Create unique temp directory
     */
    protected function createUniqueTempDirectory(): string
    {
        $tmpdir = ilFileUtils::ilTempnam();
        ilFileUtils::makeDirParents($tmpdir);
        $this->logger->info('New temp directory: ' . $tmpdir);
        return $tmpdir;
    }

    protected function createTargetDirectory(string $a_tmpdir): string
    {
        $final_dir = $a_tmpdir . "/" . $this->target_directory;
        ilFileUtils::makeDirParents($final_dir);
        $this->logger->info('New final directory: ' . $final_dir);
        return $final_dir;
    }

    /**
     * Copy files
     */
    protected function copyFiles(string $tmpdir, ilCalendarRessourceStorageCopyDefinition $definition): void
    {
        foreach ($definition->getCopyDefinitions() as $copy_task) {
            if(!is_null($copy_task[ilCalendarRessourceStorageCopyDefinition::COPY_RESSOURCE_ID])) {
                $this->copyWithRId($tmpdir, $copy_task);
            } else {
                $this->copyWithAbsolutePath($tmpdir, $copy_task);
            }
        }
    }

    protected function copyWithRId(string $tmpdir, array $copy_task)
    {
        $target_dir = $copy_task[ilCalendarRessourceStorageCopyDefinition::COPY_TARGET_DIR];
        $rid = $copy_task[ilCalendarRessourceStorageCopyDefinition::COPY_RESSOURCE_ID];
        $resource_identification = $this->irss->manage()->find($rid);

        if(is_null($resource_identification)) {
            $this->logger->notice('Cannot ressource identification of rid: ' . $rid);
            return;
        }

        $this->logger->debug('Creating directory: ' . $tmpdir . '/' . dirname($target_dir));
        ilFileUtils::makeDirParents($tmpdir . '/' . dirname($target_dir));
        $this->logger->debug('Copying ressource with id: ' . $rid . ' to ' . $tmpdir . '/' . $target_dir);

        file_put_contents(
            $tmpdir . '/' . $copy_task[ilCalendarRessourceStorageCopyDefinition::COPY_TARGET_DIR],
            $this->irss->consume()->stream($resource_identification)->getStream()
        );
    }

    protected function copyWithAbsolutePath(string $tmpdir, array $copy_task)
    {
        if (!file_exists($copy_task[ilCalendarCopyDefinition::COPY_SOURCE_DIR])) {
            $this->logger->notice('Cannot find file: ' . $copy_task[ilCalendarCopyDefinition::COPY_SOURCE_DIR]);
            return;
        }

        $this->logger->debug('Creating directory: ' . $tmpdir . '/' . dirname($copy_task[ilCalendarCopyDefinition::COPY_TARGET_DIR]));
        ilFileUtils::makeDirParents(
            $tmpdir . '/' . dirname($copy_task[ilCalendarCopyDefinition::COPY_TARGET_DIR])
        );

        $this->logger->debug(
            'Copying from: ' .
            $copy_task[ilCalendarCopyDefinition::COPY_SOURCE_DIR] .
            ' to ' .
            $tmpdir . '/' . $copy_task[ilCalendarCopyDefinition::COPY_TARGET_DIR]
        );

        copy(
            $copy_task[ilCalendarCopyDefinition::COPY_SOURCE_DIR],
            $tmpdir . '/' . $copy_task[ilCalendarCopyDefinition::COPY_TARGET_DIR]
        );
    }

    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds(): int
    {
        return 30;
    }
}
