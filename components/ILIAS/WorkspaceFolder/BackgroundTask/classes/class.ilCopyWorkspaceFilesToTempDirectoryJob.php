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

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Types\ListType;
use ILIAS\BackgroundTasks\Types\TupleType;

/**
 * Description of class class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCopyWorkspaceFilesToTempDirectoryJob extends AbstractJob
{
    private ?ilLogger $logger = null;
    protected string $target_directory;

    public function __construct()
    {
        $this->logger = ilLoggerFactory::getLogger("pwsp");
    }

    public function getInputTypes(): array
    {
        return
            [
                new SingleType(ilWorkspaceCopyDefinition::class)
            ];
    }

    public function getOutputType(): Type
    {
        return new SingleType(StringValue::class);
    }

    public function isStateless(): bool
    {
        return true;
    }

    public function run(array $input, Observer $observer): Value
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
     * Create unique temp directory
     * @return string absolute path to new temp directory
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

    protected function copyFiles(
        string $tmpdir,
        ilWorkspaceCopyDefinition $definition
    ): void {
        foreach ($definition->getCopyDefinitions() as $copy_task) {
            if (!file_exists($copy_task[ilWorkspaceCopyDefinition::COPY_SOURCE_DIR])) {
                // if the "file" to be copied is an empty folder the directory has to be created so it will be contained in the download zip
                $is_empty_folder = preg_match_all("/\/$/", $copy_task[ilWorkspaceCopyDefinition::COPY_TARGET_DIR]);
                if ($is_empty_folder) {
                    mkdir($tmpdir . '/' . $copy_task[ilWorkspaceCopyDefinition::COPY_TARGET_DIR]);
                    $this->logger->notice('Empty folder has been created: ' . $tmpdir . '/' . $copy_task[ilWorkspaceCopyDefinition::COPY_SOURCE_DIR]);
                } else {
                    $this->logger->notice('Cannot find file: ' . $copy_task[ilWorkspaceCopyDefinition::COPY_SOURCE_DIR]);
                }
                continue;
            }
            $this->logger->debug('Creating directory: ' . $tmpdir . '/' . dirname($copy_task[ilWorkspaceCopyDefinition::COPY_TARGET_DIR]));
            ilFileUtils::makeDirParents(
                $tmpdir . '/' . dirname($copy_task[ilWorkspaceCopyDefinition::COPY_TARGET_DIR])
            );

            $this->logger->debug(
                'Copying from: ' .
                $copy_task[ilWorkspaceCopyDefinition::COPY_SOURCE_DIR] .
                ' to ' .
                $tmpdir . '/' . $copy_task[ilWorkspaceCopyDefinition::COPY_TARGET_DIR]
            );

            copy(
                $copy_task[ilWorkspaceCopyDefinition::COPY_SOURCE_DIR],
                $tmpdir . '/' . $copy_task[ilWorkspaceCopyDefinition::COPY_TARGET_DIR]
            );
        }
    }

    public function getExpectedTimeOfTaskInSeconds(): int
    {
        return 30;
    }
}
