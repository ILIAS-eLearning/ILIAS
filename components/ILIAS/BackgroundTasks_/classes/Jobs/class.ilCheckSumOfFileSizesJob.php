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
class ilCheckSumOfFileSizesJob extends AbstractJob
{
    private ?ilLogger $logger;
    protected \ilSetting $settings;


    /**
     * Construct
     */
    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->cal();
        $this->settings = new ilSetting("fold");
    }


    /**
     * @inheritDoc
     */
    public function getInputTypes(): array
    {
        return
            [
                new SingleType(ilCopyDefinition::class),
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
     * @todo use filesystem service
     */
    public function run(array $input, \ILIAS\BackgroundTasks\Observer $observer): Value
    {
        $this->logger->debug('Start checking adherence to maxsize!');
        $this->logger->dump($input);
        $definition = $input[0];
        $object_ref_ids = $definition->getObjectRefIds();

        // get global limit (max sum of individual file-sizes) from file settings
        $size_limit = (int) $this->settings->get("bgtask_download_limit", '0');
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
        global $DIC;
        $tree = $DIC['tree'];

        // parse folders
        foreach ($a_ref_ids as $ref_id) {
            if (!$this->validateAccess($ref_id)) {
                continue;
            }

            // we are only interested in folders and files
            switch (ilObject::_lookupType($ref_id, true)) {
                case "fold":
                    // get child objects
                    $subtree = $tree->getChildsByTypeFilter($ref_id, array("fold", "file"));
                    if (count($subtree) > 0) {
                        $child_ref_ids = array();
                        foreach ($subtree as $child) {
                            $child_ref_ids[] = $child["ref_id"];
                        }
                        $this->calculateRecursive($child_ref_ids, $a_file_size);
                    }
                    break;

                case "file":
                    $a_file_size += ilObjFileAccess::_lookupFileSize($ref_id);
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
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        if (!$ilAccess->checkAccess("read", "", $ref_id)) {
            return false;
        }
        return !ilObject::_isInTrash($ref_id);
    }


    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds(): int
    {
        return 30;
    }
}
