<?php

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Value;

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilCopyFilesToTempDirectoryJob extends AbstractJob
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
                new SingleType(ilCopyDefinition::class),
            ];
    }


    /**
     * @return SingleType
     * @todo output should be file type
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
     *
     * @param Value    $input
     * @param Observer $observer
     */
    public function run(array $input, Observer $observer)
    {
        $definition = $input[0];

        $this->logger->info('Called copy files job');

        $this->target_directory = $definition->getTempDir();

        // create temp directory 
        $tmpdir = $this->createUniqueTempDirectory();
        $targetdir = $this->createTargetDirectory($tmpdir);

        // copy files from source to temp directory
        //$this->copyFiles($targetdir, $input[0]);
        $this->copyFiles($targetdir, $definition);

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
     *       Create unique temp directory
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
     *
     * @param string           $tmpdir
     * @param ilCopyDefinition $definition
     */
    protected function copyFiles($tmpdir, ilCopyDefinition $definition)
    {
        foreach ($definition->getCopyDefinitions() as $copy_task) {
            if (!file_exists($copy_task[ilCopyDefinition::COPY_SOURCE_DIR])) {
                // if the "file" to be copied is an empty folder the directory has to be created so it will be contained in the download zip
                $is_empty_folder = preg_match_all("/\/$/", $copy_task[ilCopyDefinition::COPY_TARGET_DIR]);
                if ($is_empty_folder) {
                    mkdir($tmpdir . '/' . $copy_task[ilCopyDefinition::COPY_TARGET_DIR]);
                    $this->logger->notice('Empty folder has been created: ' . $tmpdir . '/' . $copy_task[ilCopyDefinition::COPY_SOURCE_DIR]);
                } else {
                    $this->logger->notice('Cannot find file: ' . $copy_task[ilCopyDefinition::COPY_SOURCE_DIR]);
                }
                continue;
            }
            $this->logger->debug('Creating directory: ' . $tmpdir . '/' . dirname($copy_task[ilCopyDefinition::COPY_TARGET_DIR]));
            ilUtil::makeDirParents(
                $tmpdir . '/' . dirname($copy_task[ilCopyDefinition::COPY_TARGET_DIR])
            );

            $this->logger->debug(
                'Copying from: ' .
                $copy_task[ilCopyDefinition::COPY_SOURCE_DIR] .
                ' to ' .
                $tmpdir . '/' . $copy_task[ilCopyDefinition::COPY_TARGET_DIR]
            );

            copy(
                $copy_task[ilCopyDefinition::COPY_SOURCE_DIR],
                $tmpdir . '/' . $copy_task[ilCopyDefinition::COPY_TARGET_DIR]
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