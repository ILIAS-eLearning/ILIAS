<?php

namespace ILIAS\File\Sanitation;

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\AggregationValues\ListValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\ListType;
use ilObjFile;

/**
 * Class SanitationReportJob
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SanitationReportJob extends AbstractJob
{

    public function run(array $input, Observer $observer)
    {
        global $DIC;

        $q = "SELECT * FROM object_data WHERE type='file'";
        $s = $DIC->database()->query($q);

        $files_ids = [];

        while ($data = $DIC->database()->fetchObject($s)) {
            $file_object = new ilObjFile($data->obj_id, false);
            $san = new FilePathSanitizer($file_object);
            if ($san->needsSanitation()) {
                $files_ids[] = (int) $data->obj_id;
            }
        }

        $list = new ListValue();
        $list->setValue($files_ids);

        return $list;
    }


    public function isStateless()
    {
        return false;
    }


    public function getExpectedTimeOfTaskInSeconds()
    {
        return 3600;
    }


    public function getInputTypes()
    {
        return array();
    }


    public function getOutputType()
    {
        return new ListType(IntegerValue::class);
    }
}
