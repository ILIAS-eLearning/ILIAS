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

namespace ILIAS\MediaCast\BackgroundTasks;

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Bucket;
use ILIAS\Filesystem\Util\LegacyPathHelper;
use ILIAS\BackgroundTasks\Implementation\Values\AggregationValues\ListValue;
use ILIAS\BackgroundTasks\Implementation\Values\ThunkValue;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DownloadAllZipInteraction extends AbstractUserInteraction
{
    public const OPTION_DOWNLOAD = 'download';
    public const OPTION_CANCEL = 'cancel';

    private ?\ilLogger $logger = null;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->mcst();
    }

    public function getInputTypes() : array
    {
        return [
            new SingleType(StringValue::class),
            new SingleType(StringValue::class)
        ];
    }


    public function getRemoveOption() : UserInteractionOption
    {
        return new UserInteractionOption('remove', self::OPTION_CANCEL);
    }

    public function getOutputType() : SingleType
    {
        return new SingleType(StringValue::class);
    }

    public function getOptions(array $input) : array
    {
        return [
            new UserInteractionOption('download', self::OPTION_DOWNLOAD)
        ];
    }

    public function interaction(
        array $input,
        Option $user_selected_option,
        Bucket $bucket
    ) : \ILIAS\BackgroundTasks\Value {
        global $DIC;
        $download_name = $input[0]; //directory name.
        $zip_name = $input[1]; // zip job

        $this->logger->debug("Interaction -> input[0] download name MUST BE FULL PATH=> " . $download_name->getValue());
        $this->logger->debug("Interaction -> input[1] zip name MUST BE THE NAME WITHOUT EXTENSION. => " . $zip_name->getValue());

        if ($user_selected_option->getValue() != self::OPTION_DOWNLOAD) {
            $this->logger->info('Download canceled');
            // delete zip file
            $filesystem = $DIC->filesystem()->temp();
            try {
                $path = LegacyPathHelper::createRelativePath($zip_name->getValue());
            } catch (\InvalidArgumentException $e) {
                $path = null;
            }
            if (!is_null($path) && $filesystem->has($path)) {
                $filesystem->deleteDir(dirname($path));
            }

            return new ThunkValue();
        }

        $this->logger->info("Delivering File.");


        $zip_name = $zip_name->getValue();

        $ending = substr($zip_name, -4);
        if ($ending != ".zip") {
            $zip_name .= ".zip";
            $this->logger->info("Add .zip extension");
        }

        //Download_name->getValue should return the complete path to the file
        //Zip name is just an string
        \ilFileDelivery::deliverFileAttached($download_name->getValue(), $zip_name);

        return new ThunkValue();
    }
}
