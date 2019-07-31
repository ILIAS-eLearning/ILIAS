<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

require_once("./Services/FileDelivery/classes/class.ilPHPOutputDelivery.php");

/**
 * Class DownloadInteger
 *
 * @package ILIAS\BackgroundTasks\Implementation\Tasks
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 *
 * Example User Interaction. You will be able to download a number in a file.
 */
class DownloadInteger extends AbstractUserInteraction
{

    /**
     * @param Value[] $input The input value of this task.
     *
     * @return Option[] Options are buttons the user can press on this interaction.
     */
    public function getOptions(Array $input)
    {
        return [
            new UserInteractionOption("download", "download"),
        ];
    }


    /**
     * @param array  $input                The input value of this task.
     * @param Option $user_selected_option The Option the user chose.
     * @param Bucket $bucket               Notify the bucket about your progress!
     *
     * @return Value
     */
    public function interaction(Array $input, Option $user_selected_option, Bucket $bucket)
    {
        /** @var IntegerValue $a */
        $integerValue = $input[0];
        global $DIC;

        if ($user_selected_option->getValue() == "download") {
            $outputter = new \ilPHPOutputDelivery();
            $outputter->start("IntegerFile");
            echo $integerValue->getValue();
            $outputter->stop();
        }

        return $integerValue;
    }


    /**
     * @return Type[] Class-Name of the IO
     */
    public function getInputTypes()
    {
        return [
            new SingleType(IntegerValue::class),
        ];
    }


    /**
     * @return Type
     */
    public function getOutputType()
    {
        return new SingleType(IntegerValue::class);
    }
}