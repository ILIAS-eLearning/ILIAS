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
 
namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

/**
 * Class DownloadInteger
 * @package ILIAS\BackgroundTasks\Implementation\Tasks
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * Example User Interaction. You will be able to download a number in a file.
 */
class DownloadInteger extends AbstractUserInteraction
{
    
    /**
     * @param Value[] $input The input value of this task.
     * @return Option[] Options are buttons the user can press on this interaction.
     */
    public function getOptions(array $input) : array
    {
        return [
            new UserInteractionOption("download", "download"),
        ];
    }
    
    /**
     * @param array  $input                The input value of this task.
     * @param Option $user_selected_option The Option the user chose.
     * @param Bucket $bucket               Notify the bucket about your progress!
     */
    public function interaction(array $input, Option $user_selected_option, Bucket $bucket) : Value
    {
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
    public function getInputTypes() : array
    {
        return [
            new SingleType(IntegerValue::class),
        ];
    }
    
    public function getOutputType() : Type
    {
        return new SingleType(IntegerValue::class);
    }
}
