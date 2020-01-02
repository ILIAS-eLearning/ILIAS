<?php

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Types\ListType;
use ILIAS\BackgroundTasks\Types\TupleType;

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilCalendarCopyFilesToTempDirectoryJob extends AbstractJob
{
    /**
     * @var ilLogger
     */
    private $logger = null;

    /**
     * @var string
     */
    protected $target_directory;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = $GLOBALS['DIC']->logger()->cal();
    }

    /**
     */
    public function getInputTypes()
    {
        return
        [
            new SingleType(ilCalendarCopyDefinition::class)
        ];
    }

    /**
     * @todo output should be file type
     * @return SingleType
     */
    public function getOutputType()
    {
        return new SingleType(StringValue::class);
    }

    public function isStateless()
    {
        return true;
    }

    /**
     * run the job
     * @param Value $input
     * @param Observer $observer
     */
    public function run(array $input, Observer $observer)
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
     * @todo refactor to new file system access
     * Create unique temp directory
     * @return string absolute path to new temp directory
     */
    protected function createUniqueTempDirectory()
    {
        $tmpdir = ilUtil::ilTempnam();
        ilUtil::makeDirParents($tmpdir);
        $this->logger->info('New temp directory: ' . $tmpdir);
        return $tmpdir;
    }

    protected function createTargetDirectory($a_tmpdir)
    {
        $final_dir = $a_tmpdir . "/" . $this->target_directory;
        ilUtil::makeDirParents($final_dir);
        $this->logger->info('New final directory: ' . $final_dir);
        return $final_dir;
    }
    
    /**
     * Copy files
     * @param string $tmpdir
     * @param ilCalendarCopyDefinition $definition
     */
    protected function copyFiles($tmpdir, ilCalendarCopyDefinition $definition)
    {
        foreach ($definition->getCopyDefinitions() as $copy_task) {
            if (!file_exists($copy_task[ilCalendarCopyDefinition::COPY_SOURCE_DIR])) {
                $this->logger->notice('Cannot find file: ' . $copy_task[ilCalendarCopyDefinition::COPY_SOURCE_DIR]);
                continue;
            }
            $this->logger->debug('Creating directory: ' . $tmpdir . '/' . dirname($copy_task[ilCalendarCopyDefinition::COPY_TARGET_DIR]));
            ilUtil::makeDirParents(
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
        return;
    }


    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds()
    {
        return 30;
    }
}
