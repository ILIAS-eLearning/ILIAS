<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BooleanValue;
use ILIAS\BackgroundTasks\Types\SingleType;

/**
 * Description of class class
 *
 * @author Lukas Zehnder <lz@studer-raimann.ch>
 *
 */
class ilCheckSumOfFileSizesJob extends AbstractJob
{

    /**
     * @var |null
     */
    private $logger = null;
    /**
     * @var ilSetting
     */
    protected $settings; // [ilSetting]


    /**
     * Construct
     */
    public function __construct()
    {
        $this->logger = $GLOBALS['DIC']->logger()->cal();
        $this->settings = new ilSetting("fold");
    }


    /**
     * @inheritDoc
     */
    public function getInputTypes()
    {
        return
            [
                new SingleType(ilCopyDefinition::class),
            ];
    }


    /**
     * @inheritDoc
     */
    public function getOutputType()
    {
        return new SingleType(ilCopyDefinition::class);
    }


    /**
     * @inheritDoc
     */
    public function isStateless()
    {
        return true;
    }


    /**
     * @inheritDoc
     * @todo use filesystem service
     */
    public function run(array $input, \ILIAS\BackgroundTasks\Observer $observer)
    {
        $this->logger->debug('Start checking adherence to maxsize!');
        $this->logger->dump($input);
        $definition = $input[0];
        $object_ref_ids = $definition->getObjectRefIds();

        // get global limit (max sum of individual file-sizes) from file settings
        $size_limit = (int) $this->settings->get("bgtask_download_limit", 0);
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
     *
     * @param array $a_ref_ids
     * @param int & $a_file_count
     * @param int & $a_file_size
     */
    protected function calculateRecursive($a_ref_ids, &$a_file_size)
    {
        global $DIC;
        $tree = $DIC['tree'];

        include_once("./Modules/File/classes/class.ilObjFileAccess.php");

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
                    $a_file_size += ilObjFileAccess::_lookupFileSize(ilObject::_lookupObjId($ref_id));
                    break;
            }
        }
    }


    /**
     * Check file access
     *
     * @param int $ref_id
     *
     * @return boolean
     */
    protected function validateAccess($ref_id)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        if (!$ilAccess->checkAccess("read", "", $ref_id)) {
            return false;
        }

        if (ilObject::_isInTrash($ref_id)) {
            return false;
        }

        return true;
    }


    /**
     * @inheritdoc
     */
    public function getExpectedTimeOfTaskInSeconds()
    {
        return 30;
    }
}