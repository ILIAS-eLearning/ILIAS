<?php declare(strict_types=1);

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

    protected string $target_directory;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->cal();
    }

    /**
     * @inheritDoc
     */
    public function getInputTypes() : array
    {
        return
            [
                new SingleType(ilCalendarCopyDefinition::class)
            ];
    }

    /**
     * @inheritDoc
     * @todo output should be file type
     */
    public function getOutputType() : Type
    {
        return new SingleType(StringValue::class);
    }

    /**
     * @inheritDoc
     */
    public function isStateless() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function run(array $input, Observer $observer) : Value
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
    protected function createUniqueTempDirectory() : string
    {
        $tmpdir = ilFileUtils::ilTempnam();
        ilFileUtils::makeDirParents($tmpdir);
        $this->logger->info('New temp directory: ' . $tmpdir);
        return $tmpdir;
    }

    protected function createTargetDirectory(string $a_tmpdir) : string
    {
        $final_dir = $a_tmpdir . "/" . $this->target_directory;
        ilFileUtils::makeDirParents($final_dir);
        $this->logger->info('New final directory: ' . $final_dir);
        return $final_dir;
    }

    /**
     * Copy files
     * @param string                   $tmpdir
     * @param ilCalendarCopyDefinition $definition
     */
    protected function copyFiles(string $tmpdir, ilCalendarCopyDefinition $definition) : void
    {
        foreach ($definition->getCopyDefinitions() as $copy_task) {
            if (!file_exists($copy_task[ilCalendarCopyDefinition::COPY_SOURCE_DIR])) {
                $this->logger->notice('Cannot find file: ' . $copy_task[ilCalendarCopyDefinition::COPY_SOURCE_DIR]);
                continue;
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
    }

    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds() : int
    {
        return 30;
    }
}
