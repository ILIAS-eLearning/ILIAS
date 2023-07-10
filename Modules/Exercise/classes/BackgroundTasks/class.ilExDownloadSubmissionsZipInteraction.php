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

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;
use ILIAS\Filesystem\Util\LegacyPathHelper;

/**
 * @author Jesús López <lopez@leifos.com>
 */
class ilExDownloadSubmissionsZipInteraction extends AbstractUserInteraction
{
    public const OPTION_DOWNLOAD = 'download';
    public const OPTION_CANCEL = 'cancel';

    protected ilLogger $logger;

    public function __construct()
    {
        $this->logger = $GLOBALS['DIC']->logger()->exc();
    }


    /**
     * @return \ILIAS\BackgroundTasks\Types\SingleType[]
     */
    public function getInputTypes(): array
    {
        return [
            new SingleType(StringValue::class),
            new SingleType(StringValue::class),
        ];
    }

    public function getRemoveOption(): Option
    {
        return new UserInteractionOption('remove', self::OPTION_CANCEL);
    }

    public function getOutputType(): Type
    {
        return new SingleType(StringValue::class);
    }

    /**
     * @return \ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption[]
     */
    public function getOptions(array $input): array
    {
        return [
            new UserInteractionOption('download', self::OPTION_DOWNLOAD),
        ];
    }

    public function interaction(
        array $input,
        Option $user_selected_option,
        Bucket $bucket
    ): Value {
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
            } catch (InvalidArgumentException $e) {
                $path = null;
            }
            if (!is_null($path) && $filesystem->has($path)) {
                $filesystem->deleteDir(dirname($path));
            }
            $out = new StringValue();
            $out->setValue($input);
            return $out;
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
        ilFileDelivery::deliverFileAttached($download_name->getValue(), $zip_name);

        $out = new StringValue();
        $out->setValue($input);
        return $out;
    }
}
