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
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BooleanValue;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

/**
 * Description of class class
 *
 * @author Lukas Zehnder <lz@studer-raimann.ch>
 *
 */
class ilCollectFilesJob extends AbstractJob
{
    private ?ilLogger $logger;
    /**
     * Holds the target mapped to the number of duplicates.
     * @see ilCollectFilesJob::getFileDirs()
     * @var array<string, int>
     */
    private static array $targets = [];

    /**
     * Construct
     */
    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->cal();
    }


    /**
     * @inheritDoc
     */
    public function getInputTypes(): array
    {
        return
            [
                new SingleType(ilCopyDefinition::class),
                new SingleType(BooleanValue::class),
            ];
    }


    /**
     * @inheritDoc
     */
    public function getOutputType(): Type
    {
        return new SingleType(ilCopyDefinition::class);
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
     * @todo use filsystem service
     */
    public function run(array $input, \ILIAS\BackgroundTasks\Observer $observer): Value
    {
        $this->logger->debug('Start collecting files!');
        $this->logger->dump($input);
        $definition = $input[0];
        $initiated_by_folder_action = $input[1]->getValue();
        $object_ref_ids = $definition->getObjectRefIds();
        $files = array();

        foreach ($object_ref_ids as $object_ref_id) {
            $object = ilObjectFactory::getInstanceByRefId($object_ref_id);
            $object_type = $object->getType();
            $object_name = $object->getTitle();
            $object_temp_dir = ""; // empty as content will be added in recurseFolder and getFileDirs

            if ($object_type == "fold") {
                $num_recursions = 0;
                $files_from_folder = self::recurseFolder($object_ref_id, $object_name, $object_temp_dir, $num_recursions, $initiated_by_folder_action);
                $files = array_merge($files, $files_from_folder);
            } elseif (($object_type == "file") && (($file_dirs = self::getFileDirs(
                $object_ref_id,
                $object_name,
                $object_temp_dir
            )) !== false)) {
                $files[] = $file_dirs;
            }
        }
        $this->logger->debug('Collected files:');
        $this->logger->dump($files);

        $num_files = 0;
        foreach ($files as $file) {
            $definition->addCopyDefinition($file['source_dir'], $file['target_dir']);
            $this->logger->debug('Added new copy definition: ' . $file['source_dir'] . ' -> ' . $file['target_dir']);

            // count files only (without empty directories)
            $is_empty_folder = preg_match_all("/\/$/", $file['target_dir']);
            if (!$is_empty_folder) {
                $num_files++;
            }
        }
        $definition->setObjectRefIds($object_ref_ids);
        $definition->setNumFiles($num_files);

        return $definition;
    }


    /**
     * @return bool|array<string, string>
     * Please note that this method must only be called ONCE in order to detect
     * duplicate entries. DO NOT call this method e.g. in an if condition and
     * then again in its body.
     */
    private static function getFileDirs($a_ref_id, $a_file_name, $a_temp_dir)
    {
        global $DIC;

        $user = $DIC->user();
        $ilAccess = $DIC->access();
        if ($ilAccess->checkAccessOfUser($user->getId(), "read", "", $a_ref_id)) {
            $file = new ilObjFile($a_ref_id);
            $source_dir = $file->getFile();
            if (@!is_file($source_dir)) {
                return false;
            }
            $target_dir = $a_temp_dir . '/' . ilFileUtils::getASCIIFilename($a_file_name);

            // #25025: allow duplicate filenames by appending an incrementing
            // number per duplicate in brackets to the name.
            // Example: test.txt, test (1).txt, test (2).txt, ...
            if (isset(self::$targets[$target_dir])) {
                $target_info = pathinfo($target_dir);
                $target_dir = $a_temp_dir . $target_info["filename"] . " (" . ++self::$targets[$target_dir] . ")." . $target_info["extension"];
            } else {
                self::$targets[$target_dir] = 0;
            }

            return [
                "source_dir" => $source_dir,
                "target_dir" => $target_dir,
            ];
        }

        return false;
    }


    /**
     * @param $ref_id
     * @param $title
     * @param $tmpdir
     *
     * @return mixed[]
     */
    private static function recurseFolder($a_ref_id, $a_folder_name, $a_temp_dir, $a_num_recursions, $a_initiated_by_folder_action): array
    {
        global $DIC;

        $num_recursions = $a_num_recursions + 1;
        $tree = $DIC->repositoryTree();
        $ilAccess = $DIC->access();
        $files = array();

        // Avoid the duplication of the uppermost folder when the download is initiated via a folder's action drop-down
        // by not including said folders name in the temp_dir path.
        if ($num_recursions <= 1 && $a_initiated_by_folder_action) {
            $temp_dir = $a_temp_dir;
        } else {
            $temp_dir = $a_temp_dir . '/' . ilFileUtils::getASCIIFilename($a_folder_name);
        }

        $subtree = $tree->getChildsByTypeFilter($a_ref_id, array("fold", "file"));

        foreach ($subtree as $child) {
            if (!$ilAccess->checkAccess("read", "", $child["ref_id"])) {
                continue;
            }
            if (ilObject::_isInTrash($child["ref_id"])) {
                continue;
            }
            if ($child["type"] == "fold") {
                $files_from_folder = self::recurseFolder($child["ref_id"], $child['title'], $temp_dir, $num_recursions, $a_initiated_by_folder_action);
                $files = array_merge($files, $files_from_folder);
            } else {
                if (($child["type"] == "file") && (($dirs = self::getFileDirs($child["ref_id"], $child['title'], $temp_dir)) !== false)) {
                    $files[] = $dirs;
                }
            }
        }
        // ensure that empty folders are also contained in the downloaded zip
        if (empty($subtree)) {
            $files[] = [
                "source_dir" => "",
                "target_dir" => $temp_dir . '/',
            ];
        }

        return $files;
    }


    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds(): int
    {
        return 30;
    }
}
