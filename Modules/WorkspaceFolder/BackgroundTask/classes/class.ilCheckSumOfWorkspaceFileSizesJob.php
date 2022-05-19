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
 * @author killing@leifos.de
 *
 */
class ilCheckSumOfWorkspaceFileSizesJob extends AbstractJob
{
    private ?ilLogger $logger = null;
    protected ilSetting $settings; // [ilSetting]
    protected ilWorkspaceTree $tree;

    public function __construct()
    {
        global $DIC;

        $user = $DIC->user();

        $this->logger = ilLoggerFactory::getLogger("pwsp");
        $this->settings = new ilSetting("fold");
        $this->tree = new ilWorkspaceTree($user->getId());
    }

    public function getInputTypes() : array
    {
        return
            [
                new SingleType(ilWorkspaceCopyDefinition::class),
            ];
    }

    public function getOutputType() : Type
    {
        return new SingleType(ilWorkspaceCopyDefinition::class);
    }

    public function isStateless() : bool
    {
        return true;
    }

    public function run(array $input, \ILIAS\BackgroundTasks\Observer $observer) : Value
    {
        $this->logger->debug('Start checking adherence to maxsize!');
        $this->logger->dump($input);
        $definition = $input[0];
        $object_wps_ids = $definition->getObjectWspIds();

        // get global limit (max sum of individual file-sizes) from file settings
        $size_limit = (int) $this->settings->get("bgtask_download_limit", '0');
        $size_limit_bytes = $size_limit * 1024 * 1024;
        $this->logger->debug('Global limit (max sum of all file-sizes) in file-settings: ' . $size_limit_bytes . ' bytes');
        // get sum of individual file-sizes
        $total_bytes = 0;
        $this->calculateRecursive($object_wps_ids, $total_bytes);
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
    protected function calculateRecursive(
        array $object_wps_ids,
        int &$a_file_size
    ) : void {
        global $DIC;
        $tree = $DIC['tree'];

        // parse folders
        foreach ($object_wps_ids as $object_wps_id) {
            if (!$this->validateAccess($object_wps_id)) {
                continue;
            }

            // we are only interested in folders and files
            $obj_id = $this->tree->lookupObjectId($object_wps_id);
            $type = ilObject::_lookupType($obj_id);
            switch ($type) {
                case "wfld":
                    // get child objects
                    $subtree = $tree->getChildsByTypeFilter($object_wps_id, array("wfld", "file"));
                    if (count($subtree) > 0) {
                        $child_wsp_ids = array();
                        foreach ($subtree as $child) {
                            $child_wsp_ids[] = $child["child"];
                        }
                        $this->calculateRecursive($child_wsp_ids, $a_file_size);
                    }
                    break;

                case "file":
                    $a_file_size += ilObjFileAccess::_lookupFileSize($obj_id);
                    break;
            }
        }
    }

    protected function validateAccess(int $wsp_id) : bool
    {
        $ilAccess = new ilWorkspaceAccessHandler($this->tree);

        if (!$ilAccess->checkAccess("read", "", $wsp_id)) {
            return false;
        }

        return true;
    }

    public function getExpectedTimeOfTaskInSeconds() : int
    {
        return 30;
    }
}
