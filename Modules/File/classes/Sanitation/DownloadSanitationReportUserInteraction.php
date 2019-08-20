<?php

namespace ILIAS\File\Sanitation;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Implementation\Values\AggregationValues\ListValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Types\ListType;
use ilObjFile;

/**
 * Class DownloadSanitationReportUserInteraction
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class DownloadSanitationReportUserInteraction extends AbstractUserInteraction
{

    const OPTION_DOWNLOAD = "download";
    const OPTION_SANITIZE = "sanitize";


    public function getInputTypes()
    {
        return [new ListType(IntegerValue::class)];
    }


    public function getOutputType()
    {
        return [];
    }


    public function getOptions(array $input)
    {
        return [
            new UserInteractionOption("download", self::OPTION_DOWNLOAD),
            // new UserInteractionOption("sanitize", self::OPTION_SANITIZE),
        ];
    }


    public function interaction(array $input, Option $user_selected_option, Bucket $bucket)
    {
        /**
         * @var $list          ListValue
         * @var $integer_value IntegerValue
         */
        $list = $input[0];

        switch ($user_selected_option->getValue()) {
            case self::OPTION_DOWNLOAD:
                $output_delivery = new \ilPHPOutputDelivery();
                $output_delivery->start($bucket->getTitle() . ".csv");
                foreach ($list->getList() as $integer_value) {
                    $obj_id = $integer_value->getValue();
                    $file_object = new ilObjFile($obj_id, false);
                    echo $file_object->getFile() . "\n\r";
                }
                $output_delivery->stop();
                break;
            case self::OPTION_SANITIZE:
                foreach ($list->getList() as $integer_value) {
                    $obj_id = $integer_value->getValue();
                    $file_object = new ilObjFile($obj_id, false);
                    $san = new FilePathSanitizer($file_object);
                    $san->sanitizeIfNeeded();
                }
                break;
        }

        return $list;
    }
}
