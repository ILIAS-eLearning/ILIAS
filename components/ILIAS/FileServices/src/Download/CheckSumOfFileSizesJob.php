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

namespace ILIAS\FileServices\Download;

use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BooleanValue;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;
use ILIAS\AccessControl\PublicInterface\Access;
use ILIAS\FileServices\Legacy\RepositoryTreeProxy;
use ILIAS\Logging\Logger\LoggerInterface;

class CheckSumOfFileSizesJob extends AbstractJob
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Access $access,
        private readonly RepositoryTreeProxy $tree,
        private readonly DownloadLimit $download_limit
    ) {
    }


    /**
     * @inheritDoc
     */
    public function getInputTypes(): array
    {
        return
            [
                new SingleType(CopyDefinition::class),
            ];
    }


    /**
     * @inheritDoc
     */
    public function getOutputType(): Type
    {
        return new SingleType(CopyDefinition::class);
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
     * @todo use filesystem service
     */
    public function run(array $input, Observer $observer): Value
    {
        $this->logger->debug('Start checking adherence to maxsize!');
        $this->logger->dump($input);
        $definition = $input[0];
        $object_ref_ids = $definition->getObjectRefIds();

        // get global limit (max sum of individual file-sizes) from file settings
        $size_limit = $this->download_limit->getDownloadLimitinMB();
        $size_limit_bytes = $size_limit * 1024 * 1024;
        $this->logger->debug('Global limit (max sum of all file-sizes) in file-settings: ' . $size_limit_bytes . ' bytes');
        // get sum of individual file-sizes
        $total_bytes = 0;
        $this->calculateRecursive($object_ref_ids, $total_bytes);
        $this->logger->debug('Calculated sum of all file-sizes: ' . $total_bytes . 'MB');
        // check if calculated total size adheres top global limit
        $adheres_to_limit = new BooleanValue();
        $adheres_to_limit->setValue(true);
        if ($total_bytes > $size_limit_bytes) {
            $adheres_to_limit->setValue(false);
        }

        $definition->setSumFileSizes($total_bytes);
        $definition->setAdheresToLimit($adheres_to_limit);

        return $definition;
    }


    /**
     * Calculates the number and size of the files being downloaded recursively.

     */
    protected function calculateRecursive(array $a_ref_ids, int &$a_file_size): void
    {
        // parse folders
        foreach ($a_ref_ids as $ref_id) {
            if (!$this->validateAccess($ref_id)) {
                continue;
            }

            switch (\ilObject::_lookupType($ref_id, true)) {
                case "fold":
                    // get child objects
                    $subtree = $this->tree->getChildsByTypeFilter($ref_id, ["fold", "file"]);
                    if (count($subtree) > 0) {
                        $child_ref_ids = [];
                        foreach ($subtree as $child) {
                            $child_ref_ids[] = $child["ref_id"];
                        }
                        $this->calculateRecursive($child_ref_ids, $a_file_size);
                    }
                    break;

                case "file":
                    $a_file_size += \ilObjFileAccess::_lookupFileSize($ref_id);
                    break;
            }
        }
    }


    /**
     * Check file access
     *
     *
     */
    protected function validateAccess(int $ref_id): bool
    {
        if (!$this->access->checkAccess("read", "", $ref_id)) {
            return false;
        }
        return !\ilObject::_isInTrash($ref_id);
    }


    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds(): int
    {
        return 30;
    }
}
